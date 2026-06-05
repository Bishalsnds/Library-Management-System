<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ForgotPassword.aspx.cs" Inherits="AdminSystem.ForgotPassword" %>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta charset="UTF-8" />
    <title>Forgot Password - Library Management System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .login-container { display: flex; min-height: 100vh; }
        .login-side { width: 340px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 40px 30px; display: flex; align-items: center; }
        .side-content h1 { font-size: 26px; margin-bottom: 10px; }
        .side-content p  { font-size: 14px; opacity: 0.85; }
        .login-wrapper  { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .login-form-box { width: 100%; max-width: 420px; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .login-form-box h1 { font-size: 24px; color: #333; margin-bottom: 8px; }
        .login-form-box p.subtitle { font-size: 14px; color: #666; margin-bottom: 24px; }
        .form-group  { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: bold; color: #555; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .field-error { color: #dc3545; font-size: 12px; display: block; margin-top: 4px; min-height: 16px; }
        .input-invalid { border-color: #dc3545 !important; }
        .input-valid   { border-color: #28a745 !important; }
        .login-btn  { width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
        .login-btn:hover { opacity: 0.9; }
        .error-message   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; display: block; }
        .success-message { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; display: block; }
        .login-footer { text-align: center; margin-top: 20px; font-size: 14px; }
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
                </div>
            </div>

            <div class="login-wrapper">
                <div class="login-form-box">
                    <asp:Panel ID="pnlRequestReset" runat="server">
                        <h1>Forgot Password?</h1>
                        <p class="subtitle">Enter your email address and we'll help you reset your password.</p>
                        <asp:Label ID="lblError"   runat="server" CssClass="error-message"   Visible="false" />
                        <asp:Label ID="lblSuccess" runat="server" CssClass="success-message" Visible="false" />
                        <div class="form-group">
                            <label>Email Address</label>
                            <asp:TextBox ID="txtEmail" runat="server" placeholder="Enter your email" />
                            <span class="field-error" id="email-error"></span>
                        </div>
                        <asp:Button ID="btnRequestReset" runat="server" Text="Request Password Reset" CssClass="login-btn" OnClick="btnRequestReset_Click" />
                        <p class="login-footer"><a href="Login.aspx">Back to Login</a></p>
                    </asp:Panel>

                    <asp:Panel ID="pnlResetPassword" runat="server" Visible="false">
                        <h1>Reset Your Password</h1>
                        <asp:Label ID="lblResetError"   runat="server" CssClass="error-message"   Visible="false" />
                        <asp:Label ID="lblResetSuccess" runat="server" CssClass="success-message" Visible="false" />
                        <div class="form-group">
                            <label>New Password</label>
                            <asp:TextBox ID="txtNewPassword" runat="server" TextMode="Password" placeholder="Enter new password" />
                            <span class="field-error" id="newpass-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <asp:TextBox ID="txtConfirmPassword" runat="server" TextMode="Password" placeholder="Confirm your password" />
                            <span class="field-error" id="confirm-error"></span>
                        </div>
                        <asp:Button ID="btnResetPassword" runat="server" Text="Reset Password" CssClass="login-btn" OnClick="btnResetPassword_Click" />
                        <p class="login-footer"><a href="Login.aspx">Back to Login</a></p>
                    </asp:Panel>
                </div>
            </div>
        </div>
    </form>

    <script>
        function validateEmail() {
            var el = document.getElementById('<%= txtEmail.ClientID %>');
            if (!el) return true;
            var val = el.value.trim();
            if (!val) { el.classList.add('input-invalid'); document.getElementById('email-error').textContent = 'Email is required.'; return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { el.classList.add('input-invalid'); document.getElementById('email-error').textContent = 'Invalid email format.'; return false; }
            el.classList.remove('input-invalid'); el.classList.add('input-valid'); document.getElementById('email-error').textContent = ''; return true;
        }
        if (document.getElementById('<%= txtEmail.ClientID %>')) {
            document.getElementById('<%= txtEmail.ClientID %>').addEventListener('blur', validateEmail);
            document.getElementById('<%= btnRequestReset.ClientID %>').addEventListener('click', function(e) { if (!validateEmail()) e.preventDefault(); });
        }
    </script>
</body>
</html>
