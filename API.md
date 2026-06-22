# 4WP Account — REST API

Base namespace: **`forwp-account/v1`**

## Get authorization URL

**GET** `/wp-json/forwp-account/v1/auth/{provider}`

Supported providers: `gmail`, `github`, `facebook`

### Response

```json
{
  "auth_url": "https://accounts.google.com/o/oauth2/v2/auth?..."
}
```

## OAuth callback

**GET** `/wp-json/forwp-account/v1/callback/{provider}?code=...&state=...`

Handled server-side. Redirects to the account page on success, or back with `?forwp_account_error=` on failure.

## Frontend usage

```javascript
const response = await fetch('/wp-json/forwp-account/v1/auth/gmail');
const data = await response.json();
if (data.auth_url) {
  window.location.href = data.auth_url;
}
```

The bundled sign-in script (`forwpAccountSignin`) uses this endpoint automatically when users click `.forwp-account-signin-btn` buttons.

## Filters

| Hook | Purpose |
|------|---------|
| `forwp_account_redirect_url` | Default URL after successful OAuth |
| `forwp_account_login_url` | Sign-in page URL for guests |
