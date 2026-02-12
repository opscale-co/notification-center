export default {
    "*.php": [
        "./vendor/bin/duster lint --dirty"
    ],
    "*.{js,vue}": [
        "eslint"
    ]
}