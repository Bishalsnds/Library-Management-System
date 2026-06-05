using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;
using ClassLibrary;

namespace Testing6
{
    // Tests for clsUser Find and Authenticate methods
    [TestClass]
    public class TestUser6
    {
        [TestMethod]
        public void ValidReturnsEmptyStringForValidData()
        {
            clsUser user = new clsUser();
            string error = user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("", error);
        }

        [TestMethod]
        public void ValidSetsFirstNameProperty()
        {
            clsUser user = new clsUser();
            user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("John", user.FirstName);
        }

        [TestMethod]
        public void ValidSetsLastNameProperty()
        {
            clsUser user = new clsUser();
            user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("Smith", user.LastName);
        }

        [TestMethod]
        public void ValidSetsEmailProperty()
        {
            clsUser user = new clsUser();
            user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("john@gmail.com", user.Email);
        }

        [TestMethod]
        public void ValidSetsRoleToStudent()
        {
            clsUser user = new clsUser();
            user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("student", user.Role);
        }

        [TestMethod]
        public void ValidSetsStatusToActive()
        {
            clsUser user = new clsUser();
            user.Valid("John", "Smith", "john@gmail.com", "S001", "password1");
            Assert.AreEqual("active", user.Status);
        }
    }
}
