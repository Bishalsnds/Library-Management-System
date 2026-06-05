using System;
using ClassLibrary;

namespace AdminSystem
{
    public partial class ForgotPassword : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void btnRequestReset_Click(object sender, EventArgs e)
        {
            lblError.Visible   = false;
            lblSuccess.Visible = false;

            string email = txtEmail.Text.Trim();

            if (email.Length == 0)
            {
                lblError.Text    = "Email is required.";
                lblError.Visible = true;
                return;
            }

            if (!email.Contains("@") || !email.Contains("."))
            {
                lblError.Text    = "Invalid email format.";
                lblError.Visible = true;
                return;
            }

            clsUserCollection users = new clsUserCollection();

            if (!users.EmailExists(email))
            {
                lblError.Text    = "Email address not found in our system.";
                lblError.Visible = true;
                return;
            }

            Session["ResetEmail"] = email;

            lblSuccess.Text    = "Email found. Please enter your new password below.";
            lblSuccess.Visible = true;

            pnlRequestReset.Visible  = false;
            pnlResetPassword.Visible = true;
        }

        protected void btnResetPassword_Click(object sender, EventArgs e)
        {
            lblResetError.Visible   = false;
            lblResetSuccess.Visible = false;

            string newPassword     = txtNewPassword.Text;
            string confirmPassword = txtConfirmPassword.Text;

            if (newPassword.Length == 0)
            {
                lblResetError.Text    = "Password is required.";
                lblResetError.Visible = true;
                return;
            }

            if (newPassword.Length < 6)
            {
                lblResetError.Text    = "Password must be at least 6 characters.";
                lblResetError.Visible = true;
                return;
            }

            if (newPassword != confirmPassword)
            {
                lblResetError.Text    = "Passwords do not match.";
                lblResetError.Visible = true;
                return;
            }

            string email = Session["ResetEmail"] as string;

            if (string.IsNullOrEmpty(email))
            {
                lblResetError.Text    = "Session expired. Please request a new password reset.";
                lblResetError.Visible = true;
                pnlRequestReset.Visible  = true;
                pnlResetPassword.Visible = false;
                return;
            }

            string hashedPassword = clsUser.HashPassword(newPassword);
            clsUserCollection users = new clsUserCollection();
            users.UpdatePassword(email, hashedPassword);

            Session.Remove("ResetEmail");

            lblResetSuccess.Text    = "Password has been reset successfully! You can now login.";
            lblResetSuccess.Visible = true;
        }
    }
}
