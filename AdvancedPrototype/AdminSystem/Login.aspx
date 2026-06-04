<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Login.aspx.cs" Inherits="AdminSystem.Login" %>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Library Management System - Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .login-container { display: flex; min-height: 100vh; }
        .login-side { width: 340px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 40px 30px; display: flex; align-items: center; }
        .side-content h1 { font-size: 26px; margin-bottom: 10px; }
        .side-content p  { font-size: 14px; opacity: 0.85; margin-bottom: 20px; }
        .features-list   { list-style: none; }
        .features-list li { padding: 6px 0; font-size: 14px; }
        .features-list li::before { content: "✓ "; }
        .login-wrapper  { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .login-form-box { width: 100%; max-width: 420px; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .login-form-box h1 { font-size: 24px; color: #333; margin-bottom: 24px; }
        .form-group  { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: bold; color: #555; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
        .field-error { color: #dc3545; font-size: 12px; display: block; margin-top: 4px; min-height: 18px; }
        .input-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 3px rgba(220,53,69,0.12) !important; }
        .input-valid   { border-color: #28a745 !important; box-shadow: 0 0 0 3px rgba(40,167,69,0.10) !important; }
        .form-options  { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; font-size: 14px; }
        .form-options a { color: #667eea; text-decoration: none; }
        .login-btn  { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
        .login-btn:hover { opacity: 0.9; }
        .error-message   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
        .login-footer { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .login-footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <form id="form1" runat="server">
        <div class="login-container">
            <div class="login-side">
                <div class="side-content">
                    <h1>Library System</h1>
                    <p>Manage books efficiently</p>
                    <ul class="features-list">
                        <li>Easy Book Access</li>
                        <li>Track Borrowings</li>
                        <li>User Friendly</li>
                    </ul>
                </div>
            </div>

            <div class="login-wrapper">
                <div class="login-form-box">
                    <h1>Login</h1>
                    <asp:Label ID="lblError" runat="server" CssClass="error-message" Visible="false" />

                    <div class="form-group">
                        <label>Email Address</label>
                        <asp:TextBox ID="txtEmail" runat="server" CssClass="form-input" placeholder="Enter your email" />
                        <span class="field-error" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <asp:TextBox ID="txtPassword" runat="server" TextMode="Password" CssClass="form-input" placeholder="Enter your password" />
                        <span class="field-error" id="password-error"></span>
                    </div>

                    <div class="form-options">
                        <label><input type="checkbox" name="rememberMe" /> Remember me</label>
                        <a href="ForgotPassword.aspx">Forgot Password?</a>
                    </div>

                    <asp:Button ID="btnLogin" runat="server" Text="Login" CssClass="login-btn" OnClick="btnLogin_Click" />

                    <p class="login-footer">
                        Don't have an account? <a href="Register.aspx">Sign up here</a>
                    </p>
                </div>
            </div>
        </div>
    </form>

    <script>
        function setError(id, errorId, msg) {
            var el = document.getElementById(id);
            if (el) { el.classList.add('input-invalid'); el.classList.remove('input-valid'); }
            var err = document.getElementById(errorId);
            if (err) err.textContent = msg;
        }
        function setValid(id, errorId) {
            var el = document.getElementById(id);
            if (el) { el.classList.remove('input-invalid'); el.classList.add('input-valid'); }
            var err = document.getElementById(errorId);
            if (err) err.textContent = '';
        }
        function validateEmail() {
            var val = document.getElementById('<%= txtEmail.ClientID %>').value.trim();
            if (!val) { setError('<%= txtEmail.ClientID %>', 'email-error', 'Email address is required.'); return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { setError('<%= txtEmail.ClientID %>', 'email-error', 'Please enter a valid email address.'); return false; }
            setValid('<%= txtEmail.ClientID %>', 'email-error');
            return true;
        }
        function validatePassword() {
            var val = document.getElementById('<%= txtPassword.ClientID %>').value;
            if (!val) { setError('<%= txtPassword.ClientID %>', 'password-error', 'Password is required.'); return false; }
            setValid('<%= txtPassword.ClientID %>', 'password-error');
            return true;
        }
        document.getElementById('<%= txtEmail.ClientID %>').addEventListener('blur', validateEmail);
        document.getElementById('<%= txtPassword.ClientID %>').addEventListener('blur', validatePassword);
        document.getElementById('<%= btnLogin.ClientID %>').addEventListener('click', function(e) {
            if (!validateEmail() | !validatePassword()) e.preventDefault();
        });
    </script>
</body>
</html>
