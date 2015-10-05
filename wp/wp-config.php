<?php
/**
 * In dieser Datei werden die Grundeinstellungen für WordPress vorgenommen.
 *
 * Zu diesen Einstellungen gehören: MySQL-Zugangsdaten, Tabellenpräfix,
 * Secret-Keys, Sprache und ABSPATH. Mehr Informationen zur wp-config.php gibt es auf der {@link http://codex.wordpress.org/Editing_wp-config.php
 * wp-config.php editieren} Seite im Codex. Die Informationen für die MySQL-Datenbank bekommst du von deinem Webhoster.
 *
 * Diese Datei wird von der wp-config.php-Erzeugungsroutine verwendet. Sie wird ausgeführt, wenn noch keine wp-config.php (aber eine wp-config-sample.php) vorhanden ist,
 * und die Installationsroutine (/wp-admin/install.php) aufgerufen wird.
 * Man kann aber auch direkt in dieser Datei alle Eingaben vornehmen und sie von wp-config-sample.php in wp-config.php umbenennen und die Installation starten.
 *
 * @package WordPress
 */

/**  MySQL Einstellungen - diese Angaben bekommst du von deinem Webhoster. */
/**  Ersetze database_name_here mit dem Namen der Datenbank, die du verwenden möchtest. */
define('DB_NAME', 'pyuashop');

/** Ersetze username_here mit deinem MySQL-Datenbank-Benutzernamen */
define('DB_USER', 'root');

/** Ersetze password_here mit deinem MySQL-Passwort */
define('DB_PASSWORD', 'eeW1Roo?th');

/** Ersetze localhost mit der MySQL-Serveradresse */
define('DB_HOST', 'localhost');

/** Der Datenbankzeichensatz der beim Erstellen der Datenbanktabellen verwendet werden soll */
define('DB_CHARSET', 'utf8');

/** Der collate type sollte nicht geändert werden */
define('DB_COLLATE', '');

/**#@+
 * Sicherheitsschlüssel
 *
 * Ändere jeden KEY in eine beliebige, möglichst einzigartige Phrase. 
 * Auf der Seite {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service} kannst du dir alle KEYS generieren lassen.
 * Bitte trage für jeden KEY eine eigene Phrase ein. Du kannst die Schlüssel jederzeit wieder ändern, alle angemeldeten Benutzer müssen sich danach erneut anmelden.
 *
 * @seit 2.6.0
 */
define('AUTH_KEY',         'ZV.A&#*N~J_MHW1!YFxxt]Z3y;<hDX@]3+0AGw_rWi`u.}Ez5AX@-f^5MZ_i~>68');
define('SECURE_AUTH_KEY',  '{L!`CUrr0/{Rt-i=jTc2R0O.FlvlXv&,F1hzy+|8J{F4f#0:+yHh0DxY7f>Bg]i~');
define('LOGGED_IN_KEY',    '#u#/|P}T%~DwBmr:k]>b`V,9m6etvI7s|D;{]*|s@Pn<#b1borcvt>qh I5/`J 3');
define('NONCE_KEY',        'Sm:a{|y4C@d_<47LwM6|<8i;H`+1n:/ocq{=#?0A9&_2,(vZL+Mi;Te6T9Ws0Anf');
define('AUTH_SALT',        'qqw>z*-8^}yhf%Ms`>< l&k@-N-DVH+Lm^pC?-<P<C+9;n9zTP+|os`ARV~)(6<V');
define('SECURE_AUTH_SALT', '+(44- [>VGKZ-xu*O qh<kC?hx,:Ex:26*`0:JCMo{ XA@cY8iv-Cg?{(cp#jn(S');
define('LOGGED_IN_SALT',   'V@diz-j#~K:;dVTbl+V*X4)Sw4$FMugXH4uHP[r2GZYwe(2V]%jq+;q#}Sl7O_0?');
define('NONCE_SALT',       'J#7ykeB9^;/M^6EC|vtOjJ_Ru;vm|+G8k?H.YpkQ>X@?>i`O4ZuSeKTV5-5&db+e');

/**#@-*/

/**
 * WordPress Datenbanktabellen-Präfix
 *
 *  Wenn du verschiedene Präfixe benutzt, kannst du innerhalb einer Datenbank
 *  verschiedene WordPress-Installationen betreiben. Nur Zahlen, Buchstaben und Unterstriche bitte!
 */
$table_prefix  = 'pyua_onlineshop_2649_';

/**
 * WordPress Sprachdatei
 *
 * Hier kannst du einstellen, welche Sprachdatei benutzt werden soll. Die entsprechende
 * Sprachdatei muss im Ordner wp-content/languages vorhanden sein, beispielsweise de_DE.mo
 * Wenn du nichts einträgst, wird Englisch genommen.
 */
define('WPLANG', 'de_DE');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

