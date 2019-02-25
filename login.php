<?php

require_once 'core/init.php';

$user = new User();
if($user->check()){
    Redirect::to('dashboard');
}

$validation = new Validation();

if(Input::exists()){
    if (Token::factory()->check(Input::get('token'))) {
        $validation = $validation->check([
            'username' => ['required'  => true],
            'password' => ['required'  => true]
        ]);

        if ($validation->passed()) {

            $username = strtolower(Input::get('username'));
            $password = Input::get('password');
            $remember = (bool)Input::get('remember');

            $login = $user->login($username, $password, $remember);

            if ($login) {
                Redirect::to('dashboard');
            } else {
                Session::flash('danger', 'Login failed, Please try again later.');
                Redirect::to('login');
            }
        }
    }
}

Helper::getHeader('Algebra Contacts', 'main-header');

require 'notifications.php';

?>

<div class="row">
    <div class="col-xs-12 col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Sign In</h3>
            </div>
            <div class="panel-body">
                <form method="post">
                    <input type="hidden" name="token" value="<?php echo Token::factory()::generate(); ?>">
                    <div class="form-group <?php echo ($validation->hasError('username')) ? 'has-error':''; ?>">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" autofocus>
                        <?php echo ($validation->hasError('username')) ? '<p class="text-danger">'.$validation->hasError('username').'</p>' :''; ?>
                    </div>
                    <div class="form-group <?php echo ($validation->hasError('password')) ? 'has-error':''; ?>">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                        <?php echo ($validation->hasError('password')) ? '<p class="text-danger">'.$validation->hasError('password').'</p>' :''; ?>
                    </div>
                    <div class="checkbox">
                        <label for="remember">
                            <input type="checkbox" id="remember" name="remember" value="true"> Remember me
                        </label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                    <p>If you don't have an account, please <a href="register.php">Register</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

Helper::getFooter();

?>