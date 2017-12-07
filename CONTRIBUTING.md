Contributing
============

Thank you for your interest in contributing to the plugin! There are several things
to keep in mind when submitting a pull request:

* Usually changes should be made against the `develop` branch, as this is where development
 takes place. The exception are patches for bugs present in both `develop` and the latest
 release. Those should be fixed against the `master` branch, so the fix can be easily
 merged into both `develop` and `master`.
* When modifying JS and CSS files, note that our minified and RTL versions of the files are
 automatically built using Grunt. The unbuilt versions of most of the files reside in the
 `/unbuilt` directory, so those are the ones that you should edit. Then you can run
 `grunt build` from the command line, and the built versions of the file will be updated
 automatically. You can also use `grunt watch` to keep the files updated automatically while
 you are working on your patch. (You can set up Grunt by running `npm install` from the
 command line. If you don't have npm, you can [get it here](https://www.npmjs.com/get-npm).)
 If you're having trouble with this, we can take care of building the files after merging
 your PR.
* Be sure that your patch conforms to the [WordPress coding
 standards](https://make.wordpress.org/core/handbook/coding-standards/). You'll also
 want to read through the [WordPoints standards](https://github.com/WordPoints/standards).
 You can automatically check this by running `dev-lib/run codesniff`.
* Make sure all unit tests pass with your changes applied, and when applicable
 include new unit tests for your code. (If you aren't comfortable writing unit tests
 that's OK. We can help you out with that, or do it ourselves if it is something
 that you really don't want to tackle.)
* You should have [`WP_DEBUG`](https://codex.wordpress.org/WP_DEBUG) on to be sure
  your code doesn't introduce any strict notices.
* Please also be sure to read the [Reporting a bug](README.md#reporting-a-bug)
 section of the README.md.

If any of that sounds daunting to you, don't be afraid to make mistakes. We'll make
helpful suggestions and corrections as needed.
