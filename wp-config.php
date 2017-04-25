<?php
/**
 * WordPress基础配置文件。
 *
 * 这个文件被安装程序用于自动生成wp-config.php配置文件，
 * 您可以不使用网站，您需要手动复制这个文件，
 * 并重命名为“wp-config.php”，然后填入相关信息。
 *
 * 本文件包含以下配置选项：
 *
 * * MySQL设置
 * * 密钥
 * * 数据库表名前缀
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/zh-cn:%E7%BC%96%E8%BE%91_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** WordPress数据库的名称 */
define('DB_NAME', 'wordpress_wp2');

/** MySQL数据库用户名 */
define('DB_USER', 'wp');

/** MySQL数据库密码 */
define('DB_PASSWORD', '123');

/** MySQL主机 */
define('DB_HOST', '127.0.0.1');

/** 创建数据表时默认的文字编码 */
define('DB_CHARSET', 'utf8mb4');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/
 * WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '=???-JPPq^*AN#98!m7[}2Y4*<X,qO5pLsZ$c9&86~D1n)p6k^h2&N(_BiO;uY4_');
define('SECURE_AUTH_KEY',  'MY6CTs`e]8[M1GOq|^/8A}_,n|&IA^boZs~O+gvy=?Y,8Rr0~Rje{ilr8PELi.fY');
define('LOGGED_IN_KEY',    ';;@-/xWsh/=8`Ji1mQ1c6;v!c[z@o#7C5FAtT.?a?Oa77q@B,0VBKI!k&3gpcAt-');
define('NONCE_KEY',        'dJRbD~,wp%UfnPyMr;26XpdU<;COD1^e]W/4x~(J<RhQZYqGg.1v>RT&kqc63K[g');
define('AUTH_SALT',        'g3}5M,/ouW`ayfLRr:l*Z%Q./c[Eh[n9/{g&/%n8Dg_LvqP]q@Y*)Ncq+Yx?/#..');
define('SECURE_AUTH_SALT', 'Np{omaxdl=lri7mYeqfU#U+I<2P%n1a 1hNZG/3]sfr$Z`u%`UNN87J%]?o2&)l.');
define('LOGGED_IN_SALT',   'FK&JfD?w6I}O]IxE0gy(6+0d~9orR)|O.TVhaE(2[Kdnu5:bMoCyM+pL<7UvzK-|');
define('NONCE_SALT',       '!U>!YKV2`IJbk=EY%7c)fq@>Sl{0TalI7rAfQp%+G/OAMw}n+mhF,_)7I&Yr/+</');

/**#@-*/

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix  = 'wp_';

/**
 * 开发者专用：WordPress调试模式。
 *
 * 将这个值改为true，WordPress将显示所有用于开发的提示。
 * 强烈建议插件开发者在开发环境中启用WP_DEBUG。
 *
 * 要获取其他能用于调试的信息，请访问Codex。
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/**
 * zh_CN本地化设置：启用ICP备案号显示
 *
 * 可在设置→常规中修改。
 * 如需禁用，请移除或注释掉本行。
 */
define('WP_ZH_CN_ICP_NUM', true);

define('WP_ALLOW_MULTISITE',true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'wp.dev.gdy.io');
define('PATH_CURRENT_SITE', '/wp2/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
/* 好了！请不要再继续编辑。请保存本文件。使用愉快！ */

/** WordPress目录的绝对路径。 */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** 设置WordPress变量和包含文件。 */
require_once(ABSPATH . 'wp-settings.php');
