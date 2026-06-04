using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using ClassLibrary;

namespace Testing3
{
    // Boundary tests for Email property
    [TestClass]
    public class TestUser3
    {
        [TestMethod]
        public void EmailValidGmail()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void EmailBlank()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void EmailNoAtSymbol()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "johngmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void EmailNotGmailDomain()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@yahoo.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void EmailMaxLengthOK()
        {
            clsUser user = new clsUser();
            string email = new string('a', 89) + "@gmail.com";
            string error = user.Valid("John", "Smith", email, "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void EmailMaxLengthPlus1()
        {
            clsUser user = new clsUser();
            string email = new string('a', 90) + "@gmail.com";
            string error = user.Valid("John", "Smith", email, "S001", "password1");
            Assert.AreNotEqual("", error);
        }
    }
}
