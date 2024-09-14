# Contributing to Tempest 🌊🌊🌊
Welcome aboard! We're excited that you are interested in contributing to the Tempest framework. We value all contributions to the project and have assembled the following resources to help you get started. Thanks for being a contributor!

## How do I...
* Ask about...
  * [🐞  An Error or Bug](#report-an-error-or-bug)
  * [💡  A Feature](#request-a-feature)
* Make...
  * [📖  A Contribution to Documentation](#contribute-documentation)
  * [🔨  A Contribution to the Code]()
* Manage...
  * [❓  Issues and Pull Requests]()
  * [📦  Sub-split packages]()
  * [✅  Releases]()

## Report an Error or Bug
To report an error or a bug, please:

* Head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) to open an issue.
* Provide as much context about the problem you are running into and the environment you are running Tempest in.
* Provide the version and, if relevant, the component you are running into issues with.
* For a shot at getting our "Perfect Storm" label, submit a PR with a failing test!

Once the issue has been opened, the Tempest team will:

<!-- TODO: Update this section with some links -->
* Label the issue appropriately.
* Assign the issue to the appropriate team member.
* Try and get a response to you as quickly as possible.

In the event that an issue is opened, but we get no response within 30 days, the issue will be closed.

## Request a Feature
Tempest is a work in progress! We recognize that some features you might benefit from or expect may be missing. If you do have a feature request, please:

* Head over to the [issue page](https://github.com/tempestphp/tempest-framework/issues) to open an issue.
* Provide as much detail about the feature you are looking for and how it might benefit you and others.

Once the feature request has been opened, the Tempest team will:

<!-- TODO: Update this section with some links -->
* Label the issue appropriately.
* Ask any clarifying question to help better understand the use case.
* If the feature requested is accepted, the Tempest team will assign the "Uncharted Waters" label. A Tempest team member or a member of the community can contribute the code for this.

> [!IMPORTANT]
> We welcome all contributions and greatly value your time and effort. To ensure your work aligns with Tempest's vision and avoids unnecessary effort, we aim to provide clear guidance and feedback throughout the process.

## Contribute Documentation
Documentation is how users learn about the framework, and developers begin to understand how Tempest works under the hood. It's critical to everything we do! Thank you in advance for your assistance in ensuring Tempest documentation is extensive, user-friendly, and up-to-date.

> [!NOTE]
> We welcome contributions of any size! Feel free to submit a pull request, even if it's just fixing a typo or adding a sentence.

To contribute to Tempest's documentation, please:
* Head over to the [Tempest docs repository](https://github.com/tempestphp/tempest-docs) to fork the project.
* Add or edit any relevant documentation in a manner consistent with the rest of the documentation.
* Re-read what you wrote and run it through a spell checker.
* Open a pull request with your changes.

Once a pull request has been opened, the Tempest team will:
* Use GitHub reviews to review your pull request.
* If necessary, ask for revisions.
* If we decide to pass on your pull request, we will thank you for your contribution and explain our decision. We appreciate all the time contributors put into Tempest!
* If your pull request is accepted, we will mark it as such and merge it into the project.

## Contribute Code
We cannot wait to get your pull request! To make the most of your time, please ensure that any contributions pertain to an approved feature request or a confirmed bug. This helps us focus on the vision for Tempest and ensuring the best developer experience.

To contribute to Tempest's code, please:
* [Setup Tempest Locally](#setup-tempest-locally)
* Make the relevant code changes.
* Run `composer qa` to ensure you are meeting the necessary style guidelines.
* Push your changes to your fork and make a pull request.

### Setup Tempest Locally
* Go to the [Tempest GitHub repository](https://github.com/tempestphp/tempest-framework) and click the **Fork** button in the top-right corner of the page. This creates a copy of the project on your GitHub account.
  * See the [GitHub Documentation](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo) for more information on forking a repository.
* Clone your forked repository by running `git clone git@github.com:{my_github_username}/tempest-framework.git`.
* Enter the Tempest directory (`cd tempest-framework`) and install the composer dependencies (`composer update`).
* You're ready to get started!

Please see below for some general guidelines relating to specific components of the framework.

## Acronym Casing
Tempest uses a modified version of the [.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043(v=vs.100)?redirectedfrom=MSDN) for acronym casing. Please see below for our guidelines:

__Do capitalize all characters of two to three character acronyms, except the first word of a camel-cased identifier.__
A class named `IPAddress` is an example of a short acronym (IP) used as the first word of a Pascal-cased identifier. A parameter named `ipAddress` is an example of a short acronym (ip) used as the first word of a camel-cased identifier.

__Do capitalize only the first character of acronyms with four or more characters, except the first word of a camel-cased identifier.__
A class named `UuidGenerator` is an example of a long acronym (Uuid) used as the first word of a Pascal-cased identifier. A parameter named `uuidGenerator` is an example of a long acronym (uuid) used as the first word of a camel-cased identifier.

__Do not capitalize any of the characters of any acronyms, whatever their length, at the beginning of a camel-cased identifier.__
A class named `Uuid` is an example of a long acronym (Uuid) used as the first word of a camel-cased identifier. A parameter named `dbUsername` is an example of a short acronym (db) used as the first word of a camel-cased identifier.

## Validation Rules
Validation rules should be `final` and `readonly`. The message returned by a validation rule should not include ending
punctuation.

### Best Practices
1. __Use of `final` and `readonly`__: Ensure that validation rules are declared as final and readonly whenever possible. This practice promotes immutability and prevents inadvertent changes to the validation logic.
2. __Error Message Formatting__:
    - __Avoid Ending Punctuation__: When crafting error messages for validation rules, refrain from including ending punctuation such as periods, exclamation marks, or question marks. This helps in maintaining a uniform style and prevents inconsistency in error message presentation.

__:white_check_mark: Good Example__
> Value should be a valid email address

__:x: Bad Example__
> Value should be a valid email address!

# Release Workflow

> [!NOTE]  
> Tempest uses sub-splits to allow components to be installed as individual packages. The following outlines how this process works.

## Workflow Steps

1. **Trigger Event**
   - When a pull request is merged, or a new tag is created, the `.github/workflows/subsplit-packages.yml` action is run.

2. **Package Information Retrieval**
   - When the `subsplit-packages.yml` is run, it calls `bin/get-packages`.
   - This PHP script uses a combination of Composer and the filesystem to return (in JSON) some information about every package. It returns the:
      - **Directory**
      - **Name**
      - **Package**
      - **Organization**
      - **Repository**

3. **Action Matrix Creation**
   - The result of the `get-packages` command is then used to create an action matrix.
   - This ensures that the next steps are performed for _every_ package discovered.

4. **Monorepo Split Action**
   - The `symplify/monorepo-split-github-action@v2.3.0` GitHub action is called for every package and provided the necessary information (destination repo, directory, etc.).
   - This action takes any changes and pushes them to the sub-split repository determined by combining the "Organization" and "Repository" values returned in step 2.
   - Depending on whether a tag is found or not, a tag is also supplied so the repository is tagged appropriately.