for %%I in (..\.) do set CurDir=%%~nI
del %userprofile%\Desktop\%CurDir%-bin-list.txt
del %userprofile%\Desktop\%CurDir%-bin.zip
for /F "delims=" %%A in ('dir /s /b ^| findstr /i ".*\\bin\\.* .*\\uploads\\.* .*\.jpg$ .*\.png$ .*\.ttf$ .*\.eot$ .*\.woff$ .*\.woff2$ .*\.otf$ .*\.svg$ .*\.gif$ .*\.swf$ .*\.wav$"') do echo %%~fA >> %userprofile%\Desktop\%CurDir%-bin-list.txt
7za a -uz0 -tzip -spf2 %userprofile%\Desktop\%CurDir%-bin.zip @%userprofile%\Desktop\%CurDir%-bin-list.txt