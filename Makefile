all: fresh build install

fresh:
	echo fresh

install: 
	echo install
	
build:
	echo build

test:
	codecept run

clean:
	rm -rf debian/icinga-editor
	rm -rf debian/*.substvars debian/*.log debian/*.debhelper debian/files debian/debhelper-build-stamp

deb:
	debuild -i -us -uc -b

.PHONY : install
	