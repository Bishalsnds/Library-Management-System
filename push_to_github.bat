@echo off
echo Pushing Library Management System to GitHub...
cd /d "%~dp0"

if not exist .git (
    git init
    git branch -M main
)

git add .
git commit -m "Update Library Management System"

git remote set-url origin https://github.com/Bishalsnds/Library-Management-System.git 2>nul || git remote add origin https://github.com/Bishalsnds/Library-Management-System.git

git push -u origin main
pause