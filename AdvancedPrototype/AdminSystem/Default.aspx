<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Default.aspx.cs" Inherits="AdminSystem.Default" %>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta charset="UTF-8" />
    <title>Dashboard - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; margin: 0; }
        .navbar { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 14px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { font-size: 20px; }
        .navbar a  { color: #fff; text-decoration: none; font-size: 14px; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .welcome   { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .welcome h2 { color: #333; margin-bottom: 10px; }
        .welcome p  { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <form id="form1" runat="server">
        <div class="navbar">
            <h1>Library Management System</h1>
            <asp:LinkButton ID="lnkLogout" runat="server" Text="Logout" OnClick="lnkLogout_Click" />
        </div>
        <div class="container">
            <div class="welcome">
                <h2>Welcome, <asp:Label ID="lblName" runat="server" /></h2>
                <p>You are logged in as: <asp:Label ID="lblRole" runat="server" /></p>
                <p style="margin-top:16px;">Use the navigation to manage the library system.</p>
            </div>
        </div>
    </form>
</body>
</html>
