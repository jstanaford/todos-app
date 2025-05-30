# Todo List Scheduler

A web-based application for managing recurring tasks and to-do items with automatic scheduling functionality.

## Features

- Create, edit, and manage to-do items
- Set up recurring tasks with various schedules (daily, weekly, monthly, yearly, or custom)
- Automatic generation of future task instances
- Clean, responsive UI with a modern design
- Docker-based deployment for easy setup

## Requirements

- Docker and Docker Compose
- Bash shell (for running the management script)

## Quick Start

1. Clone this repository:
   ```bash
   git clone https://github.com/yourusername/todo-list-scheduler.git
   cd todo-list-scheduler
   ```

2. Run the start script (this will handle all setup and initialization):
   ```bash
   chmod +x manage.sh
   ./manage.sh start
   ```

3. Access the application at http://localhost:8000

## Management Commands

The `manage.sh` script provides various commands to help you manage the application:

- `./manage.sh start` - Set up and start all containers (includes automatic initialization)
- `./manage.sh stop` - Stop all containers
- `./manage.sh restart` - Restart all containers (includes automatic re-initialization)
- `./manage.sh restart-app` - Restart only the Laravel app container (faster than full restart)
- `./manage.sh generate-instances [days]` - Generate todo instances for the specified number of days (default: 730)
- `./manage.sh run-scheduler` - Run the Laravel scheduler manually
- `./manage.sh setup-cron` - Set up a cron job on the host machine to run the scheduler
- `./manage.sh remove-cron` - Remove the scheduler cron job from the host machine
- `./manage.sh clear-cache` - Clear Laravel and asset caches
- `./manage.sh build-assets` - Build frontend assets
- `./manage.sh logs [scheduler]` - View logs (specify 'scheduler' to view scheduler logs)

## Scheduler Functionality

The application includes a scheduler that automatically generates instances of recurring to-do items. The scheduler runs:

1. Every hour within the Laravel application container
2. Can be set up to run via cron on the host machine using `./manage.sh setup-cron`

## Architecture

The application consists of the following components:

- **Laravel App Container**: Runs the Laravel application and serves the web interface
- **MariaDB Container**: Stores all the todo data
- **Scheduler**: Runs within the Laravel container to generate recurring todo instances

## Development

### Directory Structure

- `src/` - Laravel application source code
- `docker/` - Docker configuration files
- `database/` - Database initialization files

### Building Assets

If you make changes to frontend assets, rebuild them using:

```bash
./manage.sh build-assets
```

This command ensures that:
1. Node.js dependencies are installed
2. Vite builds the assets correctly
3. The manifest file is created properly

### Asset Issues

If you encounter issues with assets not loading:

1. Check that the Vite manifest exists:
   ```bash
   docker exec laravel_app test -f /var/www/html/public/build/manifest.json && echo "Manifest exists" || echo "Manifest missing"
   ```

2. Rebuild the assets:
   ```bash
   ./manage.sh build-assets
   ```

3. If issues persist, try restarting just the app:
   ```bash
   ./manage.sh restart-app
   ```

### Clearing Cache

To clear all caches after making changes:

```bash
./manage.sh clear-cache
```

## Troubleshooting

If you encounter issues:

1. Check the logs: `./manage.sh logs`
2. Restart the containers: `./manage.sh restart` or `./manage.sh restart-app`
3. Clear the caches: `./manage.sh clear-cache`
4. Rebuild assets: `./manage.sh build-assets`
5. Ensure your Docker installation is working properly

## License

[MIT License](LICENSE)
