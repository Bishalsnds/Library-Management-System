using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using ClassLibrary;

namespace Testing1
{
    // Boundary tests for FirstName property
    [TestClass]
    public class TestUser1
    {
        [TestMethod]
        public void FirstNameMinLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("AB", "Smith", "test@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void FirstNameMinLengthMinus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid("A", "Smith", "test@gmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void FirstNameMaxLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid(new string('A', 50), "Smith", "test@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void FirstNameMaxLengthPlus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid(new string('A', 51), "Smith", "test@gmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void FirstNameBlank()
        {
            clsUser user = new clsUser();
            string error = user.Valid("", "Smith", "test@gmail.com", "S001", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void FirstNameMidLength()
        {
            clsUser user = new clsUser();
            string error = user.Valid(new string('A', 25), "Smith", "test@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }
    }
}
