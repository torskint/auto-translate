@echo off
echo Tape "c" pour commit/push ou "q" pour quitter

set current_dir=%cd%
cd %current_dir%

set GIT_PATH="C:\Program Files\Git\bin\git.exe"
set BRANCH=origin master

:P
set ACTION=
set /P ACTION=Action: %=%

if "%ACTION%"=="c" (
	%GIT_PATH% add -A
	%GIT_PATH% commit -am "Auto-committed on %date% %time%"
	rem %GIT_PATH% pull %BRANCH%
	
	set TAG_ACTION=
	set /P TAG_ACTION="Ajouter un tag ? (y/n)": %=%

	if /I "%TAG_ACTION%"=="y" (
		set TAG_NAME=
		set /P TAG_NAME="Nom du tag (ex: v1.0.0)": %=%

		%GIT_PATH% push %BRANCH%
		%GIT_PATH% tag %TAG_NAME%
		%GIT_PATH% push origin %TAG_NAME%
		echo ✅ Commit + Push + Tag "%TAG_NAME%" envoyés
	) else (
		%GIT_PATH% push %BRANCH%
		echo ✅ Commit + Push envoyés sans tag
	)
)

rem Quitter
if "%ACTION%"=="q" exit /b
goto P
