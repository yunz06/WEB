# NEWS HUB — Image asset notes

The current public site reads article photos only from `upload/public/uploads/articles/` and the `articles.thumbnail` / `article_images.image_path` database fields. It does not load the old vector artwork from `upload/public/uploads/editorial/`.

The legacy `editorial/` vector artwork has been removed from the current application bundle because it was a source of generic images that did not match individual stories. The starter photos in `articles/` are also illustrative editorial assets; verify permission/licensing before public commercial use. Remove the matching remote folder from the hosting file manager after deploying the updated source if it remains on the server.

The `seed_*.jpg` files are starter photographs, not guaranteed photographs of the exact event described. Keep an illustrative-image label where that is the case. For a specific event, product, person, venue or announcement, use a photograph you have permission to publish and upload it through the article editor.

Before publishing any photo, verify its source, license and any required attribution independently. The previous asset notes did not consistently describe the current bytes in the image directory, so this file intentionally does not repeat unverified author/license claims.
