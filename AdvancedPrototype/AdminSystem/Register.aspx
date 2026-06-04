<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Register.aspx.cs" Inherits="AdminSystem.Register" %>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up - Library Management System</title>
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
        .login-form-box { width: 100%; max-width: 460px; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .login-form-box h1 { font-size: 24px; color: #333; margin-bottom: 24px; }
        .form-group  { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 14px; font-weight: bold; color: #555; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
        .field-error { color: #dc3545; font-size: 12px; display: block; margin-top: 4px; min-height: 16px; }
        .input-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 3px rgba(220,53,69,0.12) !important; }
        .input-valid   { border-color: #28a745 !important; box-shadow: 0 0 0 3px rgba(40,167,69,0.10) !important; }
        .password-strength { margin-top: 4px; font-size: 12px; font-weight: 600; }
        .strength-weak { color: #dc3545; } .strength-fair { color: #fd7e14; } .strength-good { color: #28a745; }
        .login-btn  { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; margin-top: 8px; }
        .login-btn:hover { opacity: 0.9; }
        .error-message   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; display: block; }
        .success-message { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; display: block; }
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
                    <p>Create your account to manage books efficiently</p>
                    <ul class="features-list">
                        <li>Easy Book Access</li>
                        <li>Track Borrowings</li>
                        <li>User Friendly</li>
                    </ul>
                </div>
            </div>

            <div class="login-wrapper">
                <div class="login-form-box">
                    <h1>Create Account</h1>
                    <asp:Label ID="lblError"   runat="server" CssClass="error-message"   Visible="false" />
                    <asp:Label ID="lblSuccess" runat="server" CssClass="success-message" Visible="false" />

                    <div class="form-group">
                        <label>First Name</label>
                        <asp:TextBox ID="txtFirstName" runat="server" placeholder="Enter your first name" />
                        <span class="field-error" id="firstName-error"></span>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <asp:TextBox ID="txtLastName" runat="server" placeholder="Enter your last name" />
                        <span class="field-error" id="lastName-error"></span>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <asp:TextBox ID="txtEmail" runat="server" placeholder="Enter your email" />
                        <span class="field-error" id="email-error"></span>
                    </div>
                    <div class="form-group">
                        <label>Student ID</label>
                        <asp:TextBox ID="txtStudentId" runat="server" placeholder="Enter your student ID" />
                        <span class="field-error" id="studentId-error"></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <asp:TextBox ID="txtPassword" runat="server" TextMode="Password" placeholder="At least 6 characters" />
                        <span class="field-error" id="password-error"></span>
                        <span class="password-strength" id="password-strength"></span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <asp:TextBox ID="txtConfirmPassword" runat="server" TextMode="Password" placeholder="Re-enter your password" />
                        <span class="field-error" id="confirm-error"></span>
                    </div>

                    <asp:Button ID="btnRegister" runat="server" Text="Create Account" CssClass="login-btn" OnClick="btnRegister_Click" />

                    <p class="login-footer">
                        Already have an account? <a href="Login.aspx">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </form>

    <script>
        function setError(id, errorId, msg) {
            var el = document.getElementById(id); if (el) { el.classList.add('input-invalid'); el.classList.remove('input-valid'); }
            var err = document.getElementById(errorId); if (err) err.textContent = msg;
        }
        function setValid(id, errorId) {
            var el = document.getElementById(id); if (el) { el.classList.remove('input-invalid'); el.classList.add('input-valid'); }
            var err = document.getElementById(errorId); if (err) err.textContent = '';
        }
        function validateFirstName() {
            var val = document.getElementById('<%= txtFirstName.ClientID %>').value.trim();
            if (!val) { setError('<%= txtFirstName.ClientID %>', 'firstName-error', 'First name is required.'); return false; }
            if (val.length < 2) { setError('<%= txtFirstName.ClientID %>', 'firstName-error', 'First name must be at least 2 characters.'); return false; }
            setValid('<%= txtFirstName.ClientID %>', 'firstName-error'); return true;
        }
        function validateLastName() {
            var val = document.getElementById('<%= txtLastName.ClientID %>').value.trim();
            if (!val) { setError('<%= txtLastName.ClientID %>', 'lastName-error', 'Last name is required.'); return false; }
            if (val.length < 2) { setError('<%= txtLastName.ClientID %>', 'lastName-error', 'Last name must be at least 2 characters.'); return false; }
            setValid('<%= txtLastName.ClientID %>', 'lastName-error'); return true;
        }
        function validateEmail() {
            var val = document.getElementById('<%= txtEmail.ClientID %>').value.trim();
            if (!val) { setError('<%= txtEmail.ClientID %>', 'email-error', 'Email address is required.'); return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { setError('<%= txtEmail.ClientID %>', 'email-error', 'Please enter a valid email address.'); return false; }
            setValid('<%= txtEmail.ClientID %>', 'email-error'); return true;
        }
        function validateStudentId() {
            var val = document.getElementById('<%= txtStudentId.ClientID %>').value.trim();
            if (!val) { setError('<%= txtStudentId.ClientID %>', 'studentId-error', 'Student ID is required.'); return false; }
            setValid('<%= txtStudentId.ClientID %>', 'studentId-error'); return true;
        }
        function validatePassword() {
            var val = document.getElementById('<%= txtPassword.ClientID %>').value;
            var strengthEl = document.getElementById('password-strength');
            if (!val) { setError('<%= txtPassword.ClientID %>', 'password-error', 'Password is required.'); strengthEl.textContent = ''; return false; }
            if (val.length < 6) { setError('<%= txtPassword.ClientID %>', 'password-error', 'Password must be at least 6 characters.'); strengthEl.textContent = 'Weak'; strengthEl.className = 'password-strength strength-weak'; return false; }
            var score = 0;
            if (val.length >= 8) score++; if (/[A-Z]/.test(val)) score++; if (/[0-9]/.test(val)) score++; if (/[^A-Za-z0-9]/.test(val)) score++;
            if (score <= 1) { strengthEl.textContent = 'Fair'; strengthEl.className = 'password-strength strength-fair'; }
            else { strengthEl.textContent = 'Strong'; strengthEl.className = 'password-strength strength-good'; }
            setValid('<%= txtPassword.ClientID %>', 'password-error'); return true;
        }
        function validateConfirm() {
            var pw = document.getElementById('<%= txtPassword.ClientID %>').value;
            var cf = document.getElementById('<%= txtConfirmPassword.ClientID %>').value;
            if (!cf) { setError('<%= txtConfirmPassword.ClientID %>', 'confirm-error', 'Please confirm your password.'); return false; }
            if (pw !== cf) { setError('<%= txtConfirmPassword.ClientID %>', 'confirm-error', 'Passwords do not match.'); return false; }
            setValid('<%= txtConfirmPassword.ClientID %>', 'confirm-error'); return true;
        }
        document.getElementById('<%= txtFirstName.ClientID %>').addEventListener('blur', validateFirstName);
        document.getElementById('<%= txtLastName.ClientID %>').addEventListener('blur', validateLastName);
        document.getElementById('<%= txtEmail.ClientID %>').addEventListener('blur', validateEmail);
        document.getElementById('<%= txtStudentId.ClientID %>').addEventListener('blur', validateStudentId);
        document.getElementById('<%= txtPassword.ClientID %>').addEventListener('blur', validatePassword);
        document.getElementById('<%= txtConfirmPassword.ClientID %>').addEventListener('blur', validateConfirm);
        document.getElementById('<%= btnRegister.ClientID %>').addEventListener('click', function(e) {
            var ok = validateFirstName() & validateLastName() & validateEmail() & validateStudentId() & validatePassword() & validateConfirm();
            if (!ok) e.preventDefault();
        });
    </script>
</body>
</html>
