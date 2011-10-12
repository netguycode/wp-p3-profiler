'-- Remove the existing zip
del wp-profiler.zip

'-- Make a temp copy
cd ..
rmdir /S /Q .\wp-profiler
xcopy /S .\profiler-plugin .\wp-profiler\

'-- Zip it
"c:\Program Files (x86)\7-Zip\7z.exe" a -r -tzip -y -xr!?svn\* -x!.svn .\profiler-plugin\wp-profiler.zip -x!build.bat wp-profiler

'-- Clean up
rmdir /S /Q .\wp-profiler
