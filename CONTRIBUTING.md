# Contributing and Maintaining

First, thank you for taking the time to contribute!

The following is a set of guidelines for contributors as well as information and instructions around our maintenance process.  The two are closely tied together in terms of how we all work together and set expectations, so while you may not need to know everything in here to submit an issue or pull request, it's best to keep them in the same document.

## Ways to contribute

Contributing isn't just writing code - it's anything that improves the project.  All contributions are managed right here on GitHub.  Here are some ways you can help:

### Reporting bugs

If you're running into an issue, please take a look through [existing issues](https://github.com/mailchimp/wordpress/issues) and [open a new one](https://github.com/mailchimp/wordpress/issues/new) if needed.  If you're able, include steps to reproduce, environment information, and screenshots/screencasts as relevant.

### Suggesting enhancements

New features and enhancements are also managed via [issues](https://github.com/mailchimp/wordpress/issues).

### Pull requests

Pull requests represent a proposed solution to a specified problem.  They should always reference an issue that describes the problem and contains discussion about the problem itself.  Discussion on pull requests should be limited to the pull request itself, i.e. code review.

### Testing

Helping to test an open source project and provide feedback on success or failure of those tests is also a helpful contribution.  You can find details on the Critical Flows and Test Cases in [this project's GitHub Wiki](https://github.com/mailchimp/wordpress/wiki).  Submitting the results of testing via our Critical Flows as a comment on a Pull Request of a specific feature or as an Issue when testing the entire project is the best approach for providing testing results.

## Workflow

The `develop` branch is the development branch which means it contains the next version to be released.  `main` contains the corresponding stable development version.  Always work on the `develop` branch and open up PRs against `develop`.

## Release instructions

1. Branch: Starting from `develop`, cut a release branch named `release/X.Y.Z` for your changes.
2. Version bump: Bump the version number in `mailchimp.php`, `readme.txt`, and any other relevant files if it does not already reflect the version being released.  Update both the plugin "Version:" property and the plugin `MCSF_VER` constant in `mailchimp.php`.
3. Changelog: Add/update the changelog in `CHANGELOG.md` and `readme.txt`.
4. Props: update `CREDITS.md` file with any new contributors, and confirm maintainers are accurate.
5. New files: Check to be sure any new files/paths that are unnecessary in the production version are included in `.gitattributes` or `.distignore`.
6. Readme updates: Make any other readme changes as necessary. `README.md` is geared toward GitHub and `readme.txt` contains WordPress.org-specific content.  The two are slightly different.
7. Merge: Make a non-fast-forward merge from your release branch to `develop` (or merge the pull request), then do the same for `develop` into `main`, ensuring you pull the most recent changes into `develop` first (`git checkout develop && git pull origin develop && git checkout main && git merge --no-ff develop`). `main` contains the stable development version.
8. Push: Push your `main` branch to GitHub (e.g. `git push origin main`).
9. Compare `main` to `develop` to ensure no additional changes were missed. Visit [REPOSITORY_URL]/compare/main...develop
10. Test the pre-release ZIP locally by downloading it from the **Build release zip** action artifact and installing it locally. Ensure this zip has all the files we expect, that it installs and activates correctly and that all basic functionality is working.
11. Either perform a regression testing utilizing the available [Critical Flows](https://github.com/mailchimp/wordpress/wiki/#critical-flows) and Test Cases or if [end-to-end tests](https://github.com/mailchimp/wordpress/actions/workflows/e2e.yml) cover a significant portion of those Critical Flows then run e2e tests.  Only proceed if everything tests successfully.
12. Release: Create a [new release](https://github.com/mailchimp/wordpress/releases/new), naming the tag and the release with the new version number, and targeting the `main` branch. Paste the changelog from `CHANGELOG.md` into the body of the release and include a link to the closed issues on the [milestone](https://github.com/mailchimp/wordpress/milestone/#?closed=1).
13. SVN: Wait for the [GitHub Action](https://github.com/mailchimp/wordpress/actions) to finish deploying to the WordPress.org repository.  If all goes well, users with SVN commit access for that plugin will receive an emailed diff of changes.
14. Check WordPress.org: Ensure that the changes are live on https://wordpress.org/plugins/mailchimp/. This may take a few minutes.
15. Close milestone: Edit the [milestone](https://github.com/mailchimp/wordpress/milestone/#) with release date (in the `Due date (optional)` field) and link to GitHub release (in the `Description` field), then close the milestone.
16. Punt incomplete items: If any open issues or PRs which were milestoned for `X.Y.Z` do not make it into the release, update their milestone to `X.Y.Z+1`, `X.Y+1.0`, `X+1.0.0` or `Future Release`.

### What to do if things go wrong

If you run into issues during the release process and things have NOT fully deployed to WordPress.org / npm / whatever external-to-GitHub location that we might be publishing to, then the best thing to do will be to delete any Tag (e.g., https://github.com/mailchimp/wordpress/releases/tag/TAGNAME) or Release that's been created, research what's wrong, and once things are resolved work on re-tagging and re-releasing on GitHub and publishing externally where needed.

If you run into issues during the release process and things HAVE deployed to WordPress.org / npm / whatever external-to-GitHub location that we might be publishing to, then the best thing to do will be to research what's wrong and once things are resolved work on a patch release and tag on GitHub and publishing externally where needed.  At the top of the changelog / release notes it's best to note that its a hotfix to resolve whatever issues were found after the previous release.
