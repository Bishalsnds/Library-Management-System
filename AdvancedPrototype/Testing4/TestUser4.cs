using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using ClassLibrary;

namespace Testing4
{
    // Boundary tests for StudentId property
    [TestClass]
    public class TestUser4
    {
        [TestMethod]
        public void StudentIdValidOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "P12345678", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void StudentIdBlank()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void StudentIdMinLengthOK()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "AB", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void StudentIdMinLengthMinus1()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "A", "password1");
            Assert.AreNotEqual("", error);
        }

        [TestMethod]
        public void StudentIdMidLength()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S1234567", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void StudentIdMaxLength()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", new string('S', 20), "password1");
            Assert.AreEqual("", error);
        }
    }
}
