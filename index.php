<?php
	require __DIR__.'/vendor/autoload.php';

	use phpish\app;
	use phpish\template;

    require __DIR__.'/path.config.php';
	require __DIR__.'/conf/'.app\ENV.'.conf.php';

    include_once MODELS_DIR . "session.php";

    function set_flash_msg($type, $msg) {
        Session::set_alert(array('msg'=>$msg, 'type'=>$type));
    }

    function display_flash_msg() {
        $alert_msg = Session::get_alert();
        if(!empty($alert_msg)) {
            $msg_block = "<div id='session_alert_msg' class='alert alert-" . $alert_msg['type'] . "'>
            <span>" . $alert_msg['msg'] . "</span>
        </div>
        <script type='text/javascript'>
            setTimeout(function () {
                    $('#session_alert_msg').remove();
                }, 10000);
        </script>";
            Session::remove_alert();
            return $msg_block;
        }
        return "";
    }

    function meekrodb_setup()
    {
        include_once MEEKRODB_PATH.'db.class.php';
        DB::$user = DB_USER;
        DB::$password = DB_PASSWORD;
        DB::$dbName = DB_DATABASE_NAME;
        DB::$encoding = 'utf8';
        DB::$error_handler = false;
        DB::$throw_exception_on_error = true;
    }

    app\any('.*', function($req) {
        session_start();
        meekrodb_setup();
        try {
            return app\next($req);
        } catch(MeekroDBException $e) {
            set_flash_msg('error', $e->getMessage());
            return app\response_302("/");
        }
    });

    //drop the slash from the end of the URL
    app\get('{path:.*}/$', function($req) {
        $url = $req['matches']['path'];
        if(empty($url))
            return app\next($req);
        return app\response_301($url);
    });

    app\path_macro(['/'], function() {
        require CONTROLLER_DIR . 'app_request_handler.php';
    });

    app\path_macro(['/review[/.*]'], function() {
        require CONTROLLER_DIR . 'review_request_handler.php';
    });

    app\path_macro(['/user/.*'], function() {
        require CONTROLLER_DIR . 'user_request_handler.php';
    });

    app\path_macro(['/feedback[/.*]'], function() {
        require CONTROLLER_DIR . 'feedback_request_handler.php';
    });

    app\path_macro(['/survey[/.*]'], function() {
        require CONTROLLER_DIR . 'survey_request_handler.php';
    });

    app\path_macro(['/org[/.*]'], function() {
        require CONTROLLER_DIR . 'org_request_handler.php';
    });

    app\path_macro(['/team[/.*]'], function() {
        require CONTROLLER_DIR . 'team_request_handler.php';
    });

    app\path_macro(['/auth/.*'], function() {
        require CONTROLLER_DIR . 'auth_request_handler.php';
    });

?>