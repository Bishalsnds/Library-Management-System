using System;
using System.Collections.Generic;

namespace ClassLibrary
{
    public class clsUserCollection
    {
        private List<clsUser> mUserList = new List<clsUser>();
        private clsUser mThisUser = new clsUser();

        public List<clsUser> UserList
        {
            get { return mUserList; }
        }

        public clsUser ThisUser
        {
            get { return mThisUser; }
            set { mThisUser = value; }
        }

        public Int32 Count
        {
            get { return mUserList.Count; }
        }

        public clsUserCollection()
        {
            PopulateList();
        }

        private void PopulateList()
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("SELECT * FROM users ORDER BY last_name, first_name");
            Int32 count = db.Execute();
            for (Int32 i = 0; i < count; i++)
            {
                clsUser u = new clsUser();
                u.Id        = Convert.ToInt32(db.GetField("id", i));
                u.FirstName = db.GetField("first_name", i).ToString();
                u.LastName  = db.GetField("last_name", i).ToString();
                u.Email     = db.GetField("email", i).ToString();
                u.StudentId = db.GetField("student_id", i).ToString();
                u.Password  = db.GetField("password", i).ToString();
                u.Role      = db.GetField("role", i).ToString();
                u.Status    = db.GetField("status", i).ToString();
                mUserList.Add(u);
            }
        }

        public Int32 Add()
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("INSERT INTO users (first_name, last_name, email, student_id, password, role, status, created_at) " +
                      "VALUES (@FirstName, @LastName, @Email, @StudentId, @Password, @Role, @Status, @CreatedAt)");
            db.AddParameter("@FirstName", mThisUser.FirstName);
            db.AddParameter("@LastName",  mThisUser.LastName);
            db.AddParameter("@Email",     mThisUser.Email);
            db.AddParameter("@StudentId", mThisUser.StudentId);
            db.AddParameter("@Password",  mThisUser.Password);
            db.AddParameter("@Role",      mThisUser.Role);
            db.AddParameter("@Status",    mThisUser.Status);
            db.AddParameter("@CreatedAt", mThisUser.CreatedAt);
            return db.ExecuteNonQuery();
        }

        public Int32 Update()
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("UPDATE users SET first_name=@FirstName, last_name=@LastName, email=@Email, " +
                      "student_id=@StudentId, password=@Password, role=@Role, status=@Status WHERE id=@Id");
            db.AddParameter("@FirstName", mThisUser.FirstName);
            db.AddParameter("@LastName",  mThisUser.LastName);
            db.AddParameter("@Email",     mThisUser.Email);
            db.AddParameter("@StudentId", mThisUser.StudentId);
            db.AddParameter("@Password",  mThisUser.Password);
            db.AddParameter("@Role",      mThisUser.Role);
            db.AddParameter("@Status",    mThisUser.Status);
            db.AddParameter("@Id",        mThisUser.Id);
            return db.ExecuteNonQuery();
        }

        public Int32 Delete()
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("DELETE FROM users WHERE id = @Id");
            db.AddParameter("@Id", mThisUser.Id);
            return db.ExecuteNonQuery();
        }

        public bool EmailExists(string email)
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("SELECT id FROM users WHERE email = @Email");
            db.AddParameter("@Email", email);
            return db.Execute() > 0;
        }

        public bool StudentIdExists(string studentId)
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("SELECT id FROM users WHERE student_id = @StudentId");
            db.AddParameter("@StudentId", studentId);
            return db.Execute() > 0;
        }

        public bool UpdatePassword(string email, string newHashedPassword)
        {
            clsDataConnection db = new clsDataConnection();
            db.SetSQL("UPDATE users SET password = @Password WHERE email = @Email");
            db.AddParameter("@Password", newHashedPassword);
            db.AddParameter("@Email",    email);
            return db.ExecuteNonQuery() > 0;
        }
    }
}
