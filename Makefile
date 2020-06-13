repoversion=$(shell LANG=C aptitude show icinga-editor | grep Version: | awk '{print $$2}')
nextversion=$(shell echo $(repoversion) | perl -ne 'chomp; print join(".", splice(@{[split/\./,$$_]}, 0, -1), map {++$$_} pop @{[split/\./,$$_]}), "\n";')

all: fresh build install

fresh:
	git pull

install: 
	echo install
	
build:
	echo build

codeception:
	sudo systemctl start selenium-chrome
	sleep 10
	codecept run
	sudo systemctl stop selenium-chrome

clean:
	rm -rf debian/icinga-editor
	rm -rf debian/*.substvars debian/*.log debian/*.debhelper debian/files debian/debhelper-build-stamp

changelog:
	git dch --ignore-branch --snapshot --auto --git-author
	git dch --ignore-branch --release --auto -N $(VERSION) --git-author

deb:
	dpkg-buildpackage -A -us -uc

newmigration:
	read -p "Enter CamelCase migration name : " migname ; ./vendor/bin/phinx create $$migname -c ./phinx-adapter.php

newseed:
	read -p "Enter CamelCase seed name : " migname ; ./vendor/bin/phinx seed:create $$migname -c ./phinx-adapter.php

doc:
	apigen generate --source src --destination docs --title "Icinga Editor" --charset UTF-8 --access-levels public --access-levels protected --php --tree

dimage:
	docker build -t vitexsoftware/icinga-editor .

drun: dimage
	docker run  -dit --name MultiFlexiBeeSetup -p 8080:80 vitexsoftware/icinga-editor
	firefox http://localhost:8080/icinga-editor?login=demo\&password=demo

vagrant:
	vagrant destroy -f
	vagrant up
	firefox http://localhost:8080/icinga-editor?login=demo\&password=demo

release:
	echo Release v$(nextversion)
	docker build -t vitexsoftware/icinga-editor:$(nextversion) .
	dch -v $(nextversion) `git log -1 --pretty=%B | head -n 1`
	debuild -i -us -uc -b
	git commit -a -m "Release v$(nextversion)"
	git tag -a $(nextversion) -m "version $(nextversion)"
	docker push vitexsoftware/icinga-editor:$(nextversion)



.PHONY : install test
	
