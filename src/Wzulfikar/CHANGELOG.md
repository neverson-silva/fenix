
0.0.4 / 2017-04-08
==================
- Fixed conditional to check if class exists

0.0.3 / 2017-04-08
==================
- **Added**
    - Added new method `handleExceptionWithWhoops`
- **Changed**
    - Using `Whoops\PlainTextHandler` when error happens in cli environment
    - use symfony response when available
    - `renderExceptionWithWhoops`: 
        - No more use of `Illuminate\Http\Response` as return value
        - Now returns `Symfony\Component\HttpFoundation\Response` (only if class exists)

0.0.2 / 2017-04-08
==================
- Changed PHP version to "^5.5.9 || ^7.0"

0.0.1 / 2017-04-08
==================
- first functional version

