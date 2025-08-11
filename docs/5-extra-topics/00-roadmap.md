---
title: Roadmap
---

Tempest's first stable version is now released! You're more than welcome to [contribute to Tempest](https://github.com/tempestphp/tempest-framework), and can even work on features in future milestones if anything is of particular interest to you. The best way to get in touch about Tempest development is to [join our Discord server](https://discord.gg/pPhpTGUMPQ).

## Experimental features

Given the size of the project, we decided to mark a couple of features as experimental. These features may still change without having to tag a new major release. Our goal is to rid all experimental components before Tempest 2.0. Here's the list of experimental features:

- [tempest/view](/main/essentials/views): you can use both [Twig](/main/essentials/views#using-twig) or [Blade](/main/essentials/views#using-blade) as alternatives.
- [The command bus](/main/essentials/console-commands): you can plug in any other command bus if you'd like.
- [Authentication and authorization](/main/features/authentication): the current implementation is very lightweight, and we welcome people to experiment with more complex implementations as third-party packages before committing to a framework-provided solution.
- [ORM](/main/essentials/database): you can use existing ORMs like [Doctrine](https://www.doctrine-project.org/) as an alternative.
- [The DateTime component](https://github.com/tempestphp/tempest-framework/tree/main/packages/datetime): you can use [Carbon](https://carbon.nesbot.com/docs/) or [Psl](https://github.com/azjezz/psl) as alternatives.
- [The mail component](/docs/features/mail): this is a newly added component in Tempest 1.4, and is kept experimental for a couple of feature releases to make sure we can fix all edge cases before calling it "stable".
- The cryptography component: this is also kept experimental for a couple of feature releases to be able to iterate on the API.

Please note that we're committed to making all of these components stable as soon as possible. To do so, we will need real-life feedback from the community. By marking these components as experimental, we acknowledge that we probably won't get it right from the get-go, and we want to be clear about that up front.

## Upcoming features

Apart from experimental features, we're also aware that Tempest isn't feature-complete yet. Below is a list of items in our priority list. Feel free to contact us via [GitHub](https://github.com/tempestphp/tempest-framework) or [Discord](https://tempestphp.com/discord) if you'd like to suggest other features, or want to help out with one of these:

- Dedicated support for API development
- HTMX support combined with tempest/view
- Form builder
- Event bus and command bus improvements (transport support, async messaging, event sourcing, …)
- Queuing and messaging components
