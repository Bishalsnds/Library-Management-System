using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using ClassLibrary;

namespace Testing2
{
    // Boundary tests for LastName property
    [TestClass]
    public class TestUser2
    {
        [TestMethod]
        public void LastNameMinLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "AB", "test@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void LastNameMinLengthMinus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "A", "test@gmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void LastNameMaxLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", new string('B', 50), "test@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void LastNameMaxLengthPlus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", new string('B', 51), "test@gmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void LastNameBlank()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "", "test@gmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void LastNameMidLength()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", new string('B', 25), "test@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }
    }
}
