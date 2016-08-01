Template: icinga-editor/SEND_MAILS_FROM
Type: string
Description: mail address of icinga admin
 Or simply press enter to use auto genrated name
Description-cs: mailová adresa administrátora icingy

Template: icinga-editor/ICINGA_SERVER_IP
Type: string
Description: IP address of ICINGA server
 Or simply press enter to use auto genrated value
Description-cs: IP adresa serveru ICINGA

Template: icinga-editor/IMPORT_CONFIG
Type: boolean
Default: true
Description: Import icinga config files ?
 Choose yes to import all config files in /etc/icinga directory into
 database. Imported files remain renamed to *.disabled
Description-cs: Importovat konfigurační soubory icingy ?
