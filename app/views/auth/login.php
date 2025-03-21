<?php
// Login view
$title = "Login - " . APP_NAME;
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-4">Login</h2>
                
                <form action="/login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="/password-recovery" class="text-decoration-none">Forgot your password?</a>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Don't have an account?</p>
                    <a href="/register" class="btn btn-link">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>
