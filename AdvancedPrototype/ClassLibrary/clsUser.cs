using System;
using System.Security.Cryptography;
using System.Text;

namespace ClassLibrary
{
    public class clsUser
    {
        private Int32 mId;
        private string mFirstName;
        private string mLastName;
        private string mEmail;
        private string mStudentId;
        private string mPassword;
        private string mRole;
        private string mStatus;
        private DateTime mCreatedAt;

        public Int32 Id
        {
            get { return mId; }
            set { mId = value; }
        }

        public string FirstName
        {
            get { return mFirstName; }
            set { mFirstName = value; }
        }

        public string LastName
        {
            get { return mLastName; }
            set { mLastName = value; }
        }

        public string Email
        {
            get { return mEmail; }
            set { mEmail = value; }
        }

        public string StudentId
        {
            get { return mStudentId; }
            set { mStudentId = value; }
        }

        public string Password
        {
            get { return mPassword; }
            set { mPassword = value; }
        }

        public string Role
        {
            get { return mRole; }
            set { mRole = value; }
        }

        public string Status
        {
            get { return mStatus; }
            set { mStatus = value; }
        }

        public DateTime CreatedAt
        {
            get { return mCreatedAt; }
            set { mCreatedAt = value; }
        }

        public static string HashPassword(string plainText)
        {
            using (SHA256 sha = SHA256.Create())
            {
                byte[] bytes = sha.ComputeHash(Encoding.UTF8.GetBytes(plainText));
                StringBuilder sb = new StringBuilder();
                foreach (byte b in bytes) sb.Append(b.ToString("x2"));
                return sb.ToString();
            }
        }

        public string Valid(string firstName, string lastName, string email, string studentId, string password)
        {
            string error = "";

            if (firstName.Length == 0)
                error += "First name is required. ";
            else if (firstName.Length < 2)
                error += "First name must be at least 2 characters. ";
            else if (firstName.Length > 50)
                error += "First name must be less than 50 characters. ";

            if (lastName.Length == 0)
                error += "Last name is required. ";
            else if (lastName.Length < 2)
                error += "Last name must be at least 2 characters. ";
            else if (lastName.Length > 50)
                error += "Last name must be less than 50 characters. ";

            if (email.Length == 0)
                error += "Email is required. ";
            else if (email.Length > 100)
                error += "Email must be less than 100 characters. ";
            else if (!email.Contains("@") || !email.Contains("."))
                error += "Invalid email format. ";
            else if (!email.EndsWith("@gmail.com"))
                error += "Only @gmail.com email addresses are allowed. ";

            if (studentId.Length == 0)
                error += "Student ID is required. ";
            else if (studentId.Length < 2)
                error += "Student ID is too short. ";

            if (password.Length == 0)
                error += "Password is required. ";
            else if (password.Length < 6)
                error += "Password must be at least 6 characters. ";
            else if (password.Length > 100)
                error += "Password must be less than 100 characters. ";

            if (error.Length == 0)
            {
                mFirstName = firstName;
                mLastName  = lastName;
                mEmail     = email;
                mStudentId = studentId;
                mPassword  = HashPassword(password);
                mRole      = "student";
                mStatus    = "active";
                mCreatedAt = DateTime.Now;
            }

            return error;
        }

        public bool Find(Int32 id)
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("SELECT * FROM users WHERE id = @Id");
            db.AddParameter("@Id", id);
            if (db.Execute() == 1)
            {
                mId        = Convert.ToInt32(db.GetField("id"));
                mFirstName = db.GetField("first_name").ToString();
                mLastName  = db.GetField("last_name").ToString();
                mEmail     = db.GetField("email").ToString();
                mStudentId = db.GetField("student_id").ToString();
                mPassword  = db.GetField("password").ToString();
                mRole      = db.GetField("role").ToString();
                mStatus    = db.GetField("status").ToString();
                mCreatedAt = Convert.ToDateTime(db.GetField("created_at"));
                return true;
            }
            return false;
        }

        public bool Authenticate(string email, string password)
        {
            string hashedPassword = HashPassword(password);
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("SELECT * FROM users WHERE email = @Email AND password = @Password");
            db.AddParameter("@Email",    email);
            db.AddParameter("@Password", hashedPassword);
            if (db.Execute() == 1)
            {
                mId        = Convert.ToInt32(db.GetField("id"));
                mFirstName = db.GetField("first_name").ToString();
                mLastName  = db.GetField("last_name").ToString();
                mEmail     = db.GetField("email").ToString();
                mStudentId = db.GetField("student_id").ToString();
                mPassword  = db.GetField("password").ToString();
                mRole      = db.GetField("role").ToString();
                mStatus    = db.GetField("status").ToString();
                return mStatus == "active";
            }
            return false;
        }
    }
}
