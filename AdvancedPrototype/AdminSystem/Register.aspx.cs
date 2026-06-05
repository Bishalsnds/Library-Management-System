using System;
using ClassLibrary;

namespace AdminSystem
{
    public partial class Register : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void btnRegister_Click(object sender, EventArgs e)
        {
            lblError.Visible   = false;
            lblSuccess.Visible = false;

            string firstName       = txtFirstName.Text.Trim();
            string lastName        = txtLastName.Text.Trim();
            string email           = txtEmail.Text.Trim();
            string studentId       = txtStudentId.Text.Trim();
            string password        = txtPassword.Text;
            string confirmPassword = txtConfirmPassword.Text;

            if (password != confirmPassword)
            {
                lblError.Text    = "Passwords do not match.";
                lblError.Visible = true;
                return;
            }

            clsUser user = new clsUser();
            string error = user.Valid(firstName, lastName, email, studentId, password);

            if (error.Length > 0)
            {
                lblError.Text    = error;
                lblError.Visible = true;
                return;
            }

            clsUserCollection users = new clsUserCollection();

            if (users.EmailExists(email))
            {
                lblError.Text    = "Email already registered.";
                lblError.Visible = true;
                return;
            }

            if (users.StudentIdExists(studentId))
            {
                lblError.Text    = "Student ID already registered.";
                lblError.Visible = true;
                return;
            }

            users.ThisUser = user;
            users.Add();

            lblSuccess.Text    = "Account created successfully! Redirecting to login...";
            lblSuccess.Visible = true;

            Response.AddHeader("Refresh", "2;url=Login.aspx");
        }
    }
}
