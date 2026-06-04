using System;

namespace AdminSystem
{
    public partial class Default : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (Session["UserId"] == null)
            {
                Response.Redirect("Login.aspx");
                return;
            }

            if (!IsPostBack)
            {
                lblName.Text = Session["UserName"].ToString();
                lblRole.Text = Session["UserRole"].ToString();
            }
        }

        protected void lnkLogout_Click(object sender, EventArgs e)
        {
            Session.Clear();
            Response.Redirect("Login.aspx");
        }
    }
}
