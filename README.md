# JamVini

**Open-source web hosting billing, automation, and business management for hosting providers.**

JamVini is a community-friendly alternative for web hosts who need clients, services, domains, invoices, orders, payments, automation, and a public website in one system they can understand and customize.

The name combines two ideas:

- **Jam**: community, cloud, and people building together.
- **Vini**: victory, progress, and shared wins.

Together, JamVini means collaborative winning.

## Why JamVini Exists

Many hosting businesses start small. They need professional billing and automation, but existing platforms can be expensive, closed, hard to customize, or not designed around local payment and domain workflows.

JamVini is being built for hosts who want:

- A system they can afford to run while growing.
- A modular platform where features can be enabled only when needed.
- A billing system that can also power the public hosting website.
- Local-first integrations such as mobile money, local banks, tax fields, and regional domain flows.
- A codebase that hosting companies, developers, and communities can inspect, improve, and adapt.

JamVini starts from East African hosting realities, but the goal is broader: a flexible open-source platform that hosting providers anywhere can shape for their own markets.

## Project Goals

- **Open core**: keep essential hosting business tools available and transparent.
- **Modular design**: clients, services, invoices, domains, payments, SMS, registrars, CMS, and themes should work as modules or plugins where possible.
- **Host-friendly customization**: make workflows understandable enough for real providers to adapt.
- **Local market support**: treat regional payments, taxes, domain reseller workflows, and language needs as first-class concerns.
- **Community ownership**: welcome contributions from hosting companies, developers, designers, translators, payment providers, and registrar experts.

## Current Modules

JamVini is still early, but the codebase already includes foundations for:

- Admin dashboard
- Client management
- Client portal
- Service and hosting product catalog
- Domain management
- Invoices and transactions
- Orders and checkout flow
- CMS pages, posts, media, and categories
- Forms and submissions
- Sliders
- SEO tools
- Knowledge base
- SMS notification plugin structure
- Payment gateway plugin structure
- Domain registrar plugin structure
- Theme system
- Installer flow
- Hook and registry system for plugins

## Tech Stack

- PHP 8.3+
- Laravel
- Blade views
- MySQL or compatible database
- Vite for frontend assets
- Plugin-based application structure

## Installation Paths

JamVini supports two setup paths.

### Release ZIP Install

This is the recommended path for normal hosting providers and cPanel-style deployments.

A release ZIP should include:

- the application source
- Composer dependencies in `vendor/`
- built frontend assets
- no `.env` file
- the web installer enabled

Upload the release ZIP to your server, extract it, point the domain to the `public` directory, then open:

```text
/install
```

The installer will:

- check minimum server requirements
- create `.env` from `.env.example` if needed
- collect database details
- generate the application key
- run core migrations
- install core plugins
- create the first admin account

### Developer Source Install

This path is for contributors and developers working from the GitHub source.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Then serve the app:

```bash
php artisan serve
```

Open the web installer:

```text
/install
```

For a production server, configure your real database and mail settings in `.env`.

## Development Status

JamVini is not yet a finished WHMCS replacement. It is an open foundation being shaped into a practical hosting business platform.

Some areas still need hardening:

- Automated tests for installer, plugins, billing, and client workflows
- Documentation for plugin authors
- Production security review
- Payment, SMS, and registrar integrations

Contributions in these areas are very welcome.

## Contributing

You do not need to be a large company to help. Useful contributions can be small:

- Fix a bug.
- Improve documentation.
- Test installation on a new environment.
- Add a payment gateway.
- Add a registrar integration.
- Improve an admin workflow.
- Translate interface text.
- Suggest better hosting business flows.
- Share requirements from your local market.

Before large changes, please open a discussion or issue so the community can align on the direction.

## Community Values

JamVini should be practical, respectful, and welcoming.

We want this project to serve hosting providers who are starting out, growing, or operating in markets that are often treated as afterthoughts by global software vendors.

The project should remain understandable, adaptable, and community-shaped.

## Origin

JamVini began from the real needs of a hosting company looking for a billing and automation system that was affordable, customizable, and better suited to East African hosting workflows.

That origin matters, but JamVini is not meant to be one company's private tool. The goal is to build a shared open-source platform that other hosting providers can use, improve, and make their own.

## License

JamVini is free software released under the GNU General Public License v3.0 or later.

See [LICENSE](LICENSE) for the project license notice. The full GNU GPL text is available from the Free Software Foundation at <https://www.gnu.org/licenses/>.
82288NM76TXGDM2IPQV2C3HUS3EWLEMD