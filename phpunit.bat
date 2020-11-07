@ECHO OFF
cls & %~dp0\vendor\bin\phpunit --configuration=%~dp0\phpunit.xml %*
