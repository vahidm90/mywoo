for %%I in (..\.) do set CurDir=%%~nI
del %userprofile%\Desktop\%CurDir%-text-list.txt
del %userprofile%\Desktop\%CurDir%-text.zip
for /F "delims=" %%A in ('dir /s /b ^| findstr ".*\.php$ .*\.PHP$ .*\.js$ .*\.JS$ .*\.css$ .*\.CSS$ .*\.bat$ .*\.BAT$ .*\.list$ .*\.LIST$ .*\.sql$ .*\.SQL$"') do echo %%~fA >> %userprofile%\Desktop\%CurDir%-text-list.txt
7za a -uz0 -tzip -spf2 %userprofile%\Desktop\%CurDir%-text.zip @%userprofile%\Desktop\%CurDir%-text-list.txt