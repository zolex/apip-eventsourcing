# Event Sourcing with API Platform 4 and Ecotone

This example project is based on the great work done in [Domain Driven Design and API Platform 3](https://github.com/mtarld/apip-ddd).

The architecture follows:
- Domain Driven Design and hexagonal architecture
- CQRS
- Event Sourcing

It runs on [frankenphp](https://frankenphp.dev/), uses [Symfony 7](https://symfony.com/), [API Platform 4](https://api-platform.com/) for exposing the API and [Ecotone](https://ecotone.tech/) for managing Event Sourcing.

## Getting Started
If you want to try to use and tweak that example, you can follow these steps:

1. Run `git clone https://github.com/alanpoulain/apip-eventsourcing` to clone the project
2. Run `make install` to install the project
3. Run `make start` to up your containers
4. Visit https://localhost/api and play with your app!

## Contributing
That implementation is pragmatic and far for being uncriticable.
It's mainly a conceptual approach to use API Platform in order to defer operations to command and query buses.

It could and should be improved, therefore feel free to submit issues and pull requests if something isn't relevant to your use cases or isn't clean enough.

To ensure that the CI will succeed whenever contributing, make sure that either static analysis and tests are successful by running `make ci`

## Authors
[Alan Poulain](https://github.com/alanpoulain)

[Mathias Arlaud](https://github.com/mtarld) with the help of [Robin Chalas](https://github.com/chalasr)

[Andreas Linden](https://github.com/zolex)
