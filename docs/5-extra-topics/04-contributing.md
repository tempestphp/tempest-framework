---
title: Contributing
keywords: "How do I"
---

Welcome aboard! We're excited that you are interested in contributing to the Tempest framework. We value all contributions to the project and have assembled the following resources to help you get started. Thanks for being a contributor!

## Report an error or bug

To report an error or a bug, please:

- Head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) to open an issue.
- Provide as much context about the problem you are running into and the environment you are running Tempest in.
- Provide the version and, if relevant, the component you are running into issues with.
- For a shot at getting our "Perfect Storm" label, submit a PR with a failing test!

Once the issue has been opened, the Tempest team will:

<!-- TODO: Update this section with some links -->

- Label the issue appropriately.
- Assign the issue to the appropriate team member.
- Try and get a response to you as quickly as possible.

In the event that an issue is opened, but we get no response within 30 days, the issue will be closed.

## Request a feature

Tempest is a work in progress. We recognize that some features you might benefit from or expect may be missing. If you do have a feature request, please:

- Head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) to open an issue.
- Provide as much detail about the feature you are looking for and how it might benefit you and others.

Once the feature request has been opened, the Tempest team will:

<!-- TODO: Update this section with some links -->

- Label the issue appropriately.
- Ask any clarifying question to help better understand the use case.
- If the feature requested is accepted, the Tempest team will assign the `{txt}Uncharted waters` label. A Tempest team member or a member of the community can contribute the code for this.

:::tip
We welcome all contributions and greatly value your time and effort. To ensure your work aligns with Tempest's vision and avoids unnecessary effort, we aim to provide clear guidance and feedback throughout the process.
:::

## Contribute documentation

Documentation is how users learn about the framework, and developers begin to understand how Tempest works under the hood. It's critical to everything we do! Thank you in advance for your assistance in ensuring Tempest documentation is extensive, user-friendly, and up-to-date.

:::tip
We welcome contributions of any size! Feel free to submit a pull request, even if it's just fixing a typo or adding a sentence. We especially value additions coming from new users' perspectives, which help make Tempest more accessible.
:::

To contribute to Tempest's documentation, please:

- [Set up Tempest locally](#setting-up-tempest-locally) and head to `/docs`.
- Add or edit any relevant documentation in a manner consistent with the rest of the documentation.
- Re-read what you wrote and run it through a spell checker.
- Optionally,
  - Head over to the [Tempest docs repository](https://github.com/tempestphp/tempest-docs) to fork the project.
  - Run `tempest docs:symlink` to link the `/docs` of your local Tempest clone to the documentation website.
  - Preview your changes by running `bun run dev`.
- Open a pull request with your changes.

Once a pull request has been opened, the Tempest team will:

- Use GitHub reviews to review your pull request.
- If necessary, ask for revisions.
- If we decide to pass on your pull request, we will thank you for your contribution and explain our decision. We appreciate all the time contributors put into Tempest!
- If your pull request is accepted, we will mark it as such and merge it into the project. It will be released in the next tagged version! 🎉

## Contribute code

So, you want to dive into the code. To make the most of your time, please ensure that any contributions pertain to an approved feature request or a confirmed bug. This helps us focus on the vision for Tempest and ensuring the best developer experience.

To contribute to Tempest's code, you will need to first [setup Tempest locally](#setting-up-tempest-locally). Then,

- Make the relevant code changes.
- Write tests that verify that your contribution works as expected.
- Run `composer qa` to ensure you are adhering to our style guidelines.
- Create a [pull request](https://github.com/tempestphp/tempest-framework/pulls) with your changes.
- If your pull request is connected to an open issue, add a line in your description that says `{txt}Fixes #xxx`, where `{txt}#xxx` is the number of the issue you're fixing.

:::tip Pull request titles
We use [conventional commits](#commit-and-merge-conventions) to automatically generate readable changelogs. You may help with this by providing a clear pull request title, which will appear in the changelog and needs to be understandable without the pull request's content as a context. Read more about this in the [pull requests](#pull-requests) section.
:::

Once a pull request has been opened, the Tempest team will:

- Use GitHub reviews to review your pull request.
- Ensure all CI pipelines are passing.
- If necessary, ask for revisions.
- If we decide to pass on your pull request, we will thank you for your contribution and explain our decision. We appreciate all the time contributors put into Tempest!
- If your pull request is accepted, we will mark it as such and merge it into the project. It will be released in the next tagged version! 🎉

### Setting up Tempest locally

- Install PHP.
- Install Composer.
- Install [Bun](https://bun.sh) or Node.
- [Fork and clone](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo) the Tempest repository.

In your terminal, run:

```sh
cd /path/to/your/clone
composer update
bun install
bun dev
```

You're ready to get started!

#### Linking your local Tempest to another local Tempest application

If you have another Tempest application with which you want to use your local version of the framework, you may do so with [composer symlinking](https://getcomposer.org/doc/05-repositories.md#path).

Add the following in your `composer.json`, replacing `{txt}/path/to/your/clone` with the absolute path to your local version of the framework:

```json
{
	// ...
	"repositories": [
		{
			"type": "path",
			"url": "/path/to/your/clone"
		}
	],
	"minimum-stability": "dev",
	"prefer-stable": true
	// ...
}
```

You may then run `{sh}composer require "tempest/framework:*"`.

If you are also working on one of the JavaScript packages, you may also symlink them to your local Tempest application by running `{sh}bun install /path/to/your/clone/package`. Note that the path must be to the actual JavaScript package, and not the root of the framework.

For instance, assuming you cloned the framework in `/Users/you/Code/forks/tempest`, the command to symlink `vite-plugin-tempest` should look like that:

```sh
bun install /Users/you/Code/forks/tempest/packages/vite-plugin-tempest
```

Do not forget to run `bun dev` in the root of your local version of the framework, so your changes can be reflected on your local application without needing to run `bun build` each time.

### Code style and conventions

Tempest uses a modified version of PSR-12. We automate the entire styling process because we know everyone is used to different standards and workflows. To see some of the rules we enforce, check out our [Mago](https://github.com/tempestphp/tempest-framework/blob/main/mago.toml) and [Rector](https://github.com/tempestphp/tempest-framework/blob/main/rector.php) configurations.

The following outlines some other guidelines we have established for Tempest.

#### `final` and `readonly` as a default

Whenever possible, classes should be `final` and `readonly`. This practice promotes immutability and prevents inadvertent changes to logic.

:::tip{tabler:bulb}
You may watch this [video](https://www.youtube.com/watch?v=HiD6CwWq5Ds&ab_channel=PHPAnnotated) to understand {x:brendt_gd}'s thoughts about using `final`.
:::

---

#### Acronym casing

Tempest uses a modified version of the [.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043(v=vs.100)?redirectedfrom=MSDN) for acronym casing. Please see below for our guidelines:

**Do capitalize all characters of two to three character acronyms, except the first word of a camel-cased identifier.**
A class named `IPAddress` is an example of a short acronym (IP) used as the first word of a Pascal-cased identifier. A parameter named `ipAddress` is an example of a short acronym (ip) used as the first word of a camel-cased identifier.

**Do capitalize only the first character of acronyms with four or more characters, except the first word of a camel-cased identifier.**
A class named `UuidGenerator` is an example of a long acronym (Uuid) used as the first word of a Pascal-cased identifier. A parameter named `uuidGenerator` is an example of a long acronym (uuid) used as the first word of a camel-cased identifier.

**Do not capitalize any of the characters of any acronyms, whatever their length, at the beginning of a camel-cased identifier.**
A class named `Uuid` is an example of a long acronym (Uuid) used as the first word of a camel-cased identifier. A parameter named `dbUsername` is an example of a short acronym (db) used as the first word of a camel-cased identifier.

---

#### Validation classes

When writing error messages for validation rules, **refrain from including ending punctuation** such as periods, exclamation marks, or question marks. This helps in maintaining a uniform style and prevents inconsistency in error message presentation.

```diff
- Value should be a valid email address!
+ Value should be a valid email address
```

---

#### Exception classes

Exception classes can be thought of as events and should be named accordingly. Use a subject-verb structure in the past tense to describe what happened (e.g., `DatabaseOperationFailed`, `StorageUsageWasForbidden`, `AuthenticatedUserWasMissing`).

- Do not suffix class names with `Exception`; the context of `throw` or `catch` language constructs makes their purpose clear.
- All exception classes must extend PHP's built-in `\Exception`.
- When appropriate, define marker interfaces such as `CacheException`, `DatabaseException`, or `FilesystemException` to group related exceptions. These interfaces must be suffixed with `Exception`.
- Set the exception message within the exception class itself—not where it is thrown.
- Override the constructor to only accept relevant context-specific input.

For instance, the following exception accepts a the relevant cache key as a constructor argument, and keeps it accessible through a public property:

```php LockAcquisitionTimedOut.php
final class LockAcquisitionTimedOut extends Exception implements CacheException
{
    public function __construct(
        public readonly string $key,
    ) {
        parent::__construct("Lock with key `{$key}` could not be acquired on time.");
    }
}
```

## AI contributions

Behind Tempest is a small group of humans who are passionate about code and this project. We welcome anyone who's passionate about PHP and programming to join Tempest, regardless of the tools they are using. At the same time, we expect a level of respect between each other.

As an example, PRs that were AI-generated without any self-review from the contributor's side don't show mutual respect, as it puts the burden of reviewing LLM-generated code on the maintainer's side. We expect each contributor to take a level of ownership and responsibility over their contributions (before they are merged), to make sure the submitted code is clean and understandable, well-tested and adhering to our code style. In turn, from our side, we will be happy to guide anyone who's eager to learn, as long as it goes both ways. We also expect each contributor to ensure that code they are contributing is compatible with the Tempest License.

In other words, you may use whatever tools you want to write code (editors, IDEs, AI chat, agents, …), but you must take ownership and responsibility over your PRs. The least we ask is that you yourself understand the code you've written or generated and are able to explain it in full.

## Release workflow

Tempest uses sub-splits to allow components to be installed as individual packages. The following outlines how this process works.

### Workflow steps

1. **Trigger event**
   - When a pull request is merged, or a new tag is created, the `.github/workflows/subsplit-packages.yml` action is run.

2. **Package information retrieval**
   - When the `subsplit-packages.yml` is run, it calls `bin/get-packages`.
   - This PHP script uses a combination of Composer and the filesystem to return (in JSON) some information about every package. It returns the:
     - **Directory**
     - **Name**
     - **Package**
     - **Organization**
     - **Repository**

3. **Action matrix creation**
   - The result of the `get-packages` command is then used to create an action matrix.
   - This ensures that the next steps are performed for _every_ package discovered.

4. **Monorepo split action**
   - The `symplify/monorepo-split-github-action@v2.3.0` GitHub action is called for every package and provided the necessary information (destination repo, directory, etc.).
   - This action takes any changes and pushes them to the sub-split repository determined by combining the "Organization" and "Repository" values returned in step 2.
   - Depending on whether a tag is found or not, a tag is also supplied so the repository is tagged appropriately.

## Commit and merge conventions

Commits must all respect the [conventional commit specification](https://www.conventionalcommits.org/en/), so the changelog and release notes are generated using the commit history.

### Commit descriptions

Commit descriptions **should not** start with an uppercase letter and should use [imperative mood](https://git.kernel.org/pub/scm/git/git.git/tree/Documentation/SubmittingPatches?h=v2.36.1#n181):

```diff
- feat(support): Adds some cool feature
+ feat(support): add some cool feature
```

### Commit scopes

Scopes are not mandatory, but are highly recommended for consistency and easy of read. The following scopes are the most commonly used:

- `feat` — for a new feature
- `fix` — for a bug fix
- `refactor` — for changes in code that are neither bug fixes or new features
- `docs` — for any change related to the documentation
- `perf` — for code refactoring that improves performance
- `test` — for code related to automatic testing
- `style` — for refactoring related to the code style (not for CSS)
- `ci` — for changes related to our continuous integration pipeline
- `chore` — for anything else

Here are some commit examples:

```
{:hl-property:feat:}({:hl-keyword:support:}): add `StringHelper` class
{:hl-property:feat:}({:hl-keyword:support/string:}): add `uuid` method
{:hl-property:perf:}({:hl-keyword:discovery:}): improve cache efficiency
{:hl-property:refactor:}({:hl-keyword:highlight:}): improve code readability
{:hl-property:docs:}: mention new `highlight` package
{:hl-property:chore:}: update dependencies
{:hl-property:style:}: apply php-cs-fixer
```

### Pull requests

Pull request titles and descriptions should be as explicit as possible to ease the review process.

Contributors are not required to respect conventional commits within pull requests, but doing so will ease the review process by removing some overhead for core contributors.

All pull requests will be renamed to the conventional commit convention if necessary before being squash-merged to keep the commit history and changelog clean.

## Release cycles

Tempest current does not follow a fixed release cycle. In general, bug fixes and minor features can be released as soon as possible. For breaking changes, though, we aim to bundle as many as possible in a single major release. 

### Milestones

Even though bug fixes and minor features can be released whenever available, we do some level of long-term planning to ensure Tempest stays on track. There should always be two active milestones, and one for future versions.

- The **current minor milestone** includes all issues that should be addressed as patch or minor versions within the current major version. Anything in this milestone should be considered "ready to work on" and can be done at any point in time before the next major release.
- The **next major milestone** includes all issues that are planned for the next major release, many will be breaking changes. Oftentimes, we'll work on both current minor and next major milestones at the same time. 
- The **next minor milestone** includes all issues that should be addressed as patch or minor versions after the next major release has been tagged.
- All other issues that don't get assigned a milestone are considered to be "unplanned". They might at one point be added to a milestone, but there's no guarantee on timing.

As an example:

- The current Tempest version is `2.14`, that means that the current minor milestone is `2.x`
- The next major release is planned for `3.0`, so the next major milestone is `3.0`
- The next minor milestone includes features that are planned after 3.0 is released, and thus go in the `3.x` milestone

For clarity, each milestone will get its corresponding name, with the target branch or tag at the end. For the previous example, the milestones are called:

- `current minor (2.x)`
- `next major (3.0)`
- `next minor (3.x)`

Finally, as we close in on tagging `next major`, features that would usually go in `current minor` can be targeted to `next major` instead, in order to avoid too many merge conflicts between the two milestones.

### Milestone deadlines

Even though we release on a non-fixed schedule, we do assign deadlines to the `next major` version. This gives all contributors a clear goal to work towards, and helps us stay on track. The dealine for `next major` also determines the end date of `current minor`