# MAppointment

*Simple and modern appointment scheduling app.*

## Features


## Roadmap

## Deploying

### Via Docker

Example `docker-compose.yml` file:	

```yaml
version: '3.5'

services:
  mappointment:
    image: mkuhlmann/mappointment:latest
    restart: always
    ports:
      - "8080:80"
    environment:
      - DB_DSN=mysql:host=db;port=3306;dbname=mappointment
      - DB_USER=mappointment
      - DB_PASSWORD=mappointment
      - JWT_SECRET=CHANGE_ME
```

### Manually

1. Make sure development environment is setup
2. Run `npm run build`
3. Deploy `server` directory to webserver of your choice, see `docker/site.conf` for nginx example
	- Make sure webroot is set to `./server/public`
	- `/api/*` should be resolved to `index.php`
	- `/admin/*` should be resolved to `admin/index.html`
	- `/*` should be resolved to `index.html`

---

## Developing

1. Make sure you have nodejs >= 16.x and php >= 8.x installed.
2. Clone repository
3. Run `npm run dev-setup` to install dependencies and setup development environment
4. Copy `.env.example` to `.env` and fill out required variables.
4. Run `npm run dev` to start development server
5. Application is running on http://localhost:3000
