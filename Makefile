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
	debuild -i -us -uc -b


phinx:
	phinx migrate -c ./phinx-adapter.php

doc:
	apigen generate --source src --destination docs --title "Icinga Editor" --charset UTF-8 --access-levels public --access-levels protected --php --tree


.PHONY : install test
	
