<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/11
 * Time: 18:53
 */

namespace Xaircraft;


class Globals
{
    const STATUS_SUCCESS = 200;
    const STATUS_FAILURE = 4000;

    const ENV_FRAMEWORK = 1001;
    const ENV_VERSION = 1002;
    const ENV_MODE = 1003;
    const ENV_HOST = 1004;
    const ENV_RUNTIME_MODE = 1005;
    const ENV_OS = 1006;
    const ENV_OS_INFO = 1007;
    const ENV_MVC_VIEW_FILE_EXTENSION = 1008;
    const ENV_DATABASE_PROVIDER = 1009;

    const OS_WIN = 2001;
    const OS_LINUX = 2002;

    const MODE_DEV = 2003;
    const MODE_PUB = 2004;

    const ROUTER_DEFAULT_TOKENS = 2005;

    const DATABASE_PROVIDER_PDO = 2006;

    //// Exception code
    const EXCEPTION_ERROR_ENVIRONMENT = 4001;
    const EXCEPTION_ERROR_ENVIRONMENT_SET_LIMIT = 4002;

    const EXCEPTION_ERROR_DATABASE = 4100;
    const EXCEPTION_ERROR_DATABASE_INVALID_FIELD = 4101;

    const EXCEPTION_ERROR_ATTRIBUTE = 4201;
    const EXCEPTION_ERROR_ARGUMENT_INVALID = 4202;

    const EXCEPTION_ERROR_AUTHENTICATION = 4300;

    const EXCEPTION_ERROR_CONSOLE = 4400;

    const EXCEPTION_ERROR_ENTITY = 4500;

    const EXCEPTION_ERROR_IO = 4600;

    const EXCEPTION_ERROR_MODEL = 4700;

    const EXCEPTION_ERROR_WEB = 4800;

    const EXCEPTION_ERROR_DAEMON = 4900;


    const RUNTIME_MODE_APACHE2HANDLER = 'apache2handler';
    const RUNTIME_MODE_CLI = 'cli';
}