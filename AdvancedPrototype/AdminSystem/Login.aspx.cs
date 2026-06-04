using System;
using ClassLibrary;

namespace AdminSystem
{
    public partial class Login : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void btnLogin_Click(object sender, EventArgs e)
        {
            string email    = txtEmail.Text.Trim();
            string password = txtPassword.Text.Trim();

            if (email.Length == 0)
            {
                lblError.Text    = "Email is required.";
                lblError.Visible = true;
                return;
            }

            if (!email.EndsWith("@gmail.com"))
            {
                lblError.Text    = "Only @gmail.com email addresses are allowed.";
                lblError.Visible = true;
                return;
            }

            if (password.Length == 0)
            {
                lblError.Text    = "Password is required.";
                lblError.Visible = true;
                return;
            }

            clsUser user = new clsUser();

            if (user.Authenticate(email, password))
            {
                Session["UserId"]    = user.Id;
                Session["UserEmail"] = user.Email;
                Session["UserName"]  = user.FirstName + " " + user.LastName;
                Session["UserRole"]  = user.Role;
                Response.Redirect("Default.aspx");
            }
            else
            {
                lblError.Text    = "Invalid email or password, or your account is inactive.";
                lblError.Visible = true;
            }
        }
    }
}
