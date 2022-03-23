Readme Generator for Symfony Console
====================================

A small tool for adding Symfony Console command documentation
to projects' Readme files.

## Configuration

1. Add a `## Usage` header to your Readme (the name of this section can be configured; see below).
2. Add the `generate-readme` command to your Application,
   e.g. in `./bin/console`:
   ```php
   $application->add(new \Samwilson\ConsoleReadmeGenerator\Command\ReadmeGenCommand());
   ```
3. Run the command, e.g. `./bin/console generate-readme`.

This will modify your `README.md` file.
The usage documentation below is an example of the output.

[![CI](https://github.com/samwilson/console-readme-generator/actions/workflows/ci.yml/badge.svg)](https://github.com/samwilson/console-readme-generator/actions/workflows/ci.yml)

## Usage

### generate-readme

Generate command documentation for a Readme file.

    generate-readme [-i|--include INCLUDE] [-r|--readme README] [-u|--usage USAGE]

* `--include` `-i` — Explicitly include a command (e.g. "app:foo") or namespace (e.g. "app:" with trailing colon).
  This option can be provided multiple times.
* `--readme` `-r` — Path (including filename) of the README file to modify.
  Default: '[CWD]/README.md'
* `--usage` `-u` — Name of the section in the README file in which to insert the documentation.
  Default: 'Usage'

## License: MIT

Copyright 2021 Sam Wilson.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software
and associated documentation files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
