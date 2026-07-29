# Sticker Pack Format

Each built-in pack is a folder under this directory. The folder name is the pack ID and must use lowercase letters, numbers, and hyphens.

```text
assets/images/stickers/
  school-days/
    manifest.json
    happy.webp
    shy.webp
```

Use transparent PNG, WebP, or JPEG files. The picker discovers every valid pack automatically.

```json
{
  "name": "School Days",
  "stickers": [
    { "id": "happy", "label": "Happy", "file": "happy.webp" },
    { "id": "shy", "label": "Shy", "file": "shy.webp" }
  ]
}
```
