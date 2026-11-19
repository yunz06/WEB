# NEWS HUB — GitHub Actions to InfinityFree

This workflow deploys the complete PHP application from `upload/` to the InfinityFree website directory (`/htdocs/`). It does not upload `LOCAL_SETUP/`, SQL files or local development tools.

## 1. Add GitHub Actions secrets

Open the repository `yunz06/Webtintuc` → **Settings → Secrets and variables → Actions → New repository secret**.

Add these secrets (copy values from InfinityFree Control Panel; never put them in source code):

| Secret | Value |
|---|---|
| `FTP_SERVER` | FTP hostname from InfinityFree FTP Details (usually `ftpupload.net`) |
| `FTP_USERNAME` | FTP username from FTP Details |
| `FTP_PASSWORD` | Hosting account / FTP password |
| `NEWS_HUB_DB_HOST` | MySQL Host Name from MySQL Databases |
| `NEWS_HUB_DB_NAME` | Full MySQL DB Name including the account prefix |
| `NEWS_HUB_DB_USER` | Full MySQL User Name |
| `NEWS_HUB_DB_PASSWORD` | Database password / hosting account password as specified by InfinityFree |
| `NEWS_HUB_DB_PORT` | Optional; usually `3306` |

Do not paste these values into issues, commits, chat messages or workflow files.

## 2. First deploy: dry run

Open **Settings → Secrets and variables → Actions → Variables → New repository variable** and add:

- Name: `INFINITYFREE_DEPLOY_ENABLED`
- Value: `false`

Then open **Actions → NEWS HUB - InfinityFree deploy → Run workflow**. Keep `dry_run` set to `true`. This validates the source, creates the deployment package in the runner and previews the FTP changes without uploading files.

Review the run logs. The workflow must pass validation and the FTP preview must point to the correct website directory. If the FTP path differs for your domain/add-on domain, check the **Domains** page in InfinityFree and update `server-dir` in the workflow before proceeding.

## 3. First real deploy

After confirming the dry run:

1. Change repository variable `INFINITYFREE_DEPLOY_ENABLED` to `true`.
2. Run the workflow manually again with `dry_run` set to `false`.
3. Confirm the deployment succeeds, then open the website and test it.

The first successful deployment publishes all files in `upload/`, including PHP, CSS, JavaScript and the committed starter images. It generates `config/database.local.php` on the runner from GitHub Secrets and uploads it securely; that file is never committed to the repository.

## 4. Automatic deploys after setup

After the first deploy, every push to `main` that changes files under `upload/` triggers validation and deployment. Changes outside `upload/` (for example `LOCAL_SETUP/`) do not trigger an FTP upload.

## 5. Safety behavior

- The workflow never imports SQL, runs migrations, changes database rows or deletes database records.
- `dangerous-clean-slate` is disabled; the action will not empty the entire `htdocs` folder.
- `config/database.local.php` is generated from Secrets and is not committed.
- FTP credentials and database credentials are stored only as GitHub Actions Secrets.
- `public/uploads/` is part of the deployment source so the initial editorial images are uploaded. Admin uploads use random filenames and are not committed to Git. Do not commit or delete user-uploaded files in that folder through Git.
- The deployment is code/assets only. Database schema and content are already initialized separately in InfinityFree phpMyAdmin; do not import seed or setup SQL again.

## 6. If the deployment fails

- FTP authentication failure: re-check FTP host/user/password in the InfinityFree FTP Details section.
- FTPS certificate/handshake failure: do not lower security silently; confirm the correct FTPS endpoint with InfinityFree support first.
- Remote path not found: check the website directory shown in InfinityFree Domains; the primary domain commonly uses `htdocs/`, while additional domains can have their own `<domain>/htdocs/` path.
- Database connection error after deploy: re-check all `NEWS_HUB_DB_*` Secrets, especially the full prefixed database name and MySQL hostname.
- Do not make the repository public credentials or copy Secrets into `database.local.php` in Git.

## 7. Manual disable / rollback

Set `INFINITYFREE_DEPLOY_ENABLED` to `false` to stop future automatic deployments. The previous source can be restored by reverting a Git commit and pushing it to `main`, after checking that the prior PHP version is compatible with the production database.
