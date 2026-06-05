using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using ClassLibrary;

namespace Testing5
{
    // Boundary tests for Password property
    [TestClass]
    public class TestUser5
    {
        [TestMethod]
        public void PasswordMinLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", "123456");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void PasswordMinLengthMinus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", "12345");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void PasswordMaxLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", new string('p', 100));
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void PasswordMaxLengthPlus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", new string('p', 101));
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void PasswordBlank()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", "");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void PasswordMidLength()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", new string('p', 50));
            Assert.AreEqual("", error);
        }
    }
}
