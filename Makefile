all: fresh build install

fresh:
	git pull

install: 
	echo install
	
build:
	echo build

codeception:
	sudo systemctl start selenium-chrome
	codecept run
	sudo systemctl stop selenium-chrome

clean:
	rm -rf debian/icinga-editor
	rm -rf debian/*.substvars debian/*.log debian/*.debhelper debian/files debian/debhelper-build-stamp

deb:
	debuild -i -us -uc -b

.PHONY : install test
	
