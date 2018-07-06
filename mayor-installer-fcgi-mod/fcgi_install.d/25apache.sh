#!/bin/bash
#

cat <<EOF
Az Apache (2) webszerver beállítása

Az elektronikus napló használata feltétlenül HTTPS protokollt igényel.
Ehhez engedélyeznie kell az ssl modul használatát, és tanusítványra is
szükség van. A telepítő "self-signed" tanusítványt generál (ehhez meg kell
adnia a szerver teljes domain nevét), valamint engedélyezi a rewrite modul
használatát is, végül létrehoz és engedélyez mayor néven egy site
konfigurációt is.

Valamint itt történik a mod_suexec és a mod_fcgid konfigurálása is.

EOF

read -n 1 -p "Az Apache web-szerver módosítható? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nAz Apache web szerver beállításait nem módosítottam.\n"; exit 1; fi

if [ "$MAYORDIR" = "" ]; then echo "A MAYORDIR változó üres. Kilépek."; exit 1; fi

echo -e "\nApache beállítások"


echo "    Tanusítvány generálás"
if [ ! -e /etc/apache2/ssl ]; then 
    mkdir /etc/apache2/ssl
fi
make-ssl-cert /usr/share/ssl-cert/ssleay.cnf /etc/apache2/ssl/apache_mayor.pem

echo "  Szükséges modulok ellenőrzése:"
echo -n "    - ModSSL engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/ssl.load ]; then
    a2enmod ssl > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi
    echo -n "    - ModReWrite engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/rewrite.load ]; then
    a2enmod rewrite > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi

echo -n "    - mod_suexec engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/suexec.load ]; then
    a2enmod suexec > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi

echo -n "    - mod_fcgid engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/fcgid.load ]; then
    a2enmod fcgid > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi

echo -n "    - mod_actions engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/actions.load ]; then
    a2enmod actions > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi


echo -n "    - mod_php kikapcsolása (a www-data php értelmezője)"
if [ -e /etc/apache2/mods-enabled/php7.0.load ]; then
    a2dismod php7.0 > /dev/null
    echo ok
else
    echo "nincs bekapcsolava"
fi

echo -n "    - mod_headers engedélyezése"
if [ ! -e /etc/apache2/mods-enabled/headers.load ]; then
    a2enmod headers > /dev/null
    echo ok
else
    echo "nincs bekapcsolava"
fi


echo -n "    - Apache2 mod_worker és mod_event, amíg a php nem lesz thread-safe. "
if [ -e /etc/apache2/mods-enabled/mpm_event.load ] || [ -e /etc/apache2/mods-enabled/mpm_worker.load ]; then
    a2dismod mpm_event > /dev/null
    a2dismod mpm_worker > /dev/null
    a2enmod mpm_prefork > /dev/null
    echo ok
else
    echo "nincs bekapcsolava"
fi

echo "ok."


echo -e "  Apache2 konfiguráció létrehozása\n"
cat > /etc/apache2/sites-available/mayor_naplo.conf <<EOT
<VirtualHost *:443>
	ServerName your.mayor.server.hu
#        ServerAlias your.mayor.server.hu    your.mayor.server.iskola.sulinet.hu


	ServerAdmin webmaster@localhost

        SuexecUserGroup mayor-user mayor-user
        DocumentRoot mayor-docroot
        ScriptAlias /wbin/ mayor-web-bin
						
        AddHandler php-fcgi .php
        Action php-fcgi /wbin/php7.fcgi	 
        AddType application/x-httpd-php .php
        
	<IfModule mod_fcgid.c>
            FcgidProcessLifeTime 3600   
            FcgidIOTimeout       320          
            FcgidMaxRequestInMem 16777216 
            FcgidMaxRequestLen   33554432   
            FcgidBusyTimeout     600        
##          FcgidOutputBufferSize 0      
            FcgidIdleTimeout     400          
        </IfModule>

#						Részletesebben:
#	    FcgidProcessLifeTime 3600		##(seconds) A php max futási ideje (a (hagyományos) mod_php-nál is ennyi)
#	    FcgidIOTimeout 320			##(seconds) A php generál valamit, elkldi a böngészőnek, vagy a böngésző küld adatot a php-nak, ennyi ideig tarthat. (pl: 120sec)  
#	    FcgidMaxRequestInMem 16777216	##(bytes) Amikor a böngésző küld adatokat a php-nak, ennyi megy a pufferbe. (16MB elég)		
#	    FcgidMaxRequestLen 33554432		##(bytes) Amikor a böngésző küld adatot a php-nak, az adat max hossza bájtokabn. (32MB elég)
#	    FcgidBusyTimeout 600		##(seconds) A php max ennyi ideig gondolkozhat egy futáson/kérésen, ha túllépi, ki lesz lőve.
#	    FcgidOutputBufferSize   0		##(bytes) Pufer, amikor a php generál valami adatot, és azt elküldené a böngészőnek. {ezt még át kéne nézni}
#	    FcgidIdleTimeout 400		##(seconds) A php-értelmező ennyi pihenés után le lesz állítva.(**)
# 						##(**)  Figyelem! Itt a "php-értelmező" külön gyermekprocesszként fut, ami az oldal meglátogatásakor indul automatikusan, 
#						##	és FcgidIdleTimeout-nyi pihenés után magától leáll.
	<IfModule autoindex>
	    IndexIgnore *
	</IfModule>

						## A php-csomagoló, és php.ini fájlok közvetlen elérésének tiltása
        <Directory mayor-web-bin >	
                Require all granted	
             <Files "php7.fcgi">		
                Require env REDIRECT_STATUS	
             </Files>
             <Files "php.ini">
                Require all denied
             </Files>
	     <Files "php.ini.mayor">
                Require all denied             
             </Files>
        </Directory>

        DirectoryIndex index.php index.html index.htm

       <Directory />		
                Options FollowSymLinks
                AllowOverride None
        </Directory>

        <Directory /var/mayor/www/>
                Options -Indexes +FollowSymLinks +MultiViews
                AllowOverride None
                # Apache 2.2 # Order allow,deny
                # Apache 2.2 # allow from all
                # Apache 2.4 # Require all granted
                <IfVersion >= 2.3>
                    Require all granted
                </IfVersion>
                <IfVersion < 2.3>
                    order allow,deny
                    allow from all
                </IfVersion>
                RewriteEngine on
                RewriteBase /
                RewriteCond %{SERVER_PORT} ^80$
                RewriteCond %{THE_REQUEST}  .*(policy=private|page=auth|page=password).*
                RewriteRule (.*)$ https://%{HTTP_HOST}/$1 [L]
        </Directory>


	SSLEngine On
#								## A gyenge titkosítások tiltása	
	SSLProtocol        all -SSLv2 -SSLv3 -TLSv1	
	SSLCertificateFile /etc/apache2/ssl/apache_mayor.pem
# #	SSLCertificateFile /etc/apache2/ssl/crt/name-cert.pem
# #	SSLCertificateKeyFile /etc/apache2/ssl/key/name-key.pem


	<IfModule mod_headers.c>		
        	Header always set Strict-Transport-Security "max-age=15552000; includeSubDomains"
	</IfModule>


	ErrorLog \${APACHE_LOG_DIR}/mayor_error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel info ssl:warn fcgid:debug

	CustomLog  \${APACHE_LOG_DIR}/mayor_access.log combined
	ServerSignature On

</VirtualHost>
EOT



SERVERNAME=""
while [ "$SERVERNAME" = "" ]
do
    read -p "  A web szerver teljes domain neve (pl: mayor.tesztsuli.hu): " SERVERNAME
done

#cat $MAYORDIR/install/base/apache2/mayor.conf | sed "s/ServerName your.mayor.server.hu/ServerName $SERVERNAME/" > /etc/apache2/sites-available/mayor.conf

# A 2.4-es apache esetén módosítani kell a konfig állományt!
APACHE_VERSION=$(dpkg -l apache2 | grep apache2 | tr -s ' ' | cut -d ' ' -f 3 | cut -d '-' -f 1 | sed 's/\..$//')

echo -e "  Apache2 finomhangolása\n"

sed -e "s/ServerName your.mayor.server.hu/ServerName $SERVERNAME/g" -i /etc/apache2/sites-available/mayor_naplo.conf
sed -e "s/SuexecUserGroup mayor-user mayor-user/SuexecUserGroup $MAYORUSER $MAYORUSER/g" -i /etc/apache2/sites-available/mayor_naplo.conf
sed -e "s/DocumentRoot mayor-docroot/DocumentRoot \/home\/$MAYORUSER\/mayor\/www\/ /g" -i /etc/apache2/sites-available/mayor_naplo.conf
sed -e "s/ScriptAlias \/wbin\/ mayor-web-bin/ScriptAlias \/wbin\/ \/home\/$MAYORUSER\/mayor\/www-bin\/ /g" -i /etc/apache2/sites-available/mayor_naplo.conf
sed -e "s/<Directory mayor-web-bin >/<Directory \/home\/$MAYORUSER\/mayor\/www-bin\/ > /g" -i /etc/apache2/sites-available/mayor_naplo.conf
sed -e "s/<Directory \/var\/mayor\/www\/>/<Directory \/home\/$MAYORUSER\/mayor\/www\/> /g" -i  /etc/apache2/sites-available/mayor_naplo.conf

echo -e " a suexec beállítása\n"
sed -e 's/\/var\/www/\/home\n\/var\/www/g' -i /etc/apache2/suexec/www-data

echo " A php-csomagoló elkészítése"
mkdir -p "/home/$MAYORUSER/mayor/www-bin/"

csomagolo="#!/bin/sh \n exec /usr/bin/php-cgi7.0 "
echo -e $csomagolo > "/home/$MAYORUSER/mayor/www-bin/php7.fcgi"
chmod +x "/home/$MAYORUSER/mayor/www-bin/php7.fcgi"

echo "  A php.ini beszerzése"
cp "/etc/php/7.0/cgi/php.ini" "/home/$MAYORUSER/mayor/www-bin/php.ini"
chown -R "$MAYORUSER:$MAYORUSER"  "/home/$MAYORUSER/mayor/www-bin"


if [ ! -e /etc/apache2/sites-enabled/mayor_naplo.conf ]; then
    echo "  A mayor site engedélyezése"
    a2ensite mayor_naplo.conf > /dev/null
fi

echo "  A web-szerver újraindítása"
/etc/init.d/apache2 restart
