import js from "@eslint/js";
import globals from "globals";
import vuePlugin from "eslint-plugin-vue";
import jsonPlugin from "@eslint/json";
import cssPlugin from "@eslint/css";
import { defineConfig } from "eslint/config";
import vueParser from "vue-eslint-parser";


export default defineConfig(
    [
    { ignores: [
        'node_modules/',
        'public/shared',
        '**/node_modules/',
        '/**/node_modules/*',
        '/**/public/vendor/*',
        'out/',
        'dist/',
        'build/',
        "**/.vscode/**", 
        "**/node_modules/**", 
        "**/dist/**", 
        "**/.gitignore",
        "**/composer.json",
        "**/vendor/**",
        '**/package-lock.json',
        '**/build/**',
        '**/ziggy.js',
        '**/eslint.config.js',
        '**/app.css'
        ] },
    { files: ["**/*.{js,mjs,cjs,vue}"], plugins: { js }, extends: ["js/recommended"] },
    { files: ["**/*.{js,mjs,cjs,vue}"], languageOptions: { globals: globals.browser } },
    {
        files: ["**/*.{js,mjs,cjs,vue}"], // áp dụng cho tất cả các file có phần mở rộng js, mjs, cjs, hoặc vue trong toàn bộ thư mục dự án
        languageOptions:{
            globals: {
                route: 'readonly', // định nghĩa biến toàn cục 'route' chỉ được đọc (không được gán giá trị mới)
                Echo: 'readonly'   // định nghĩa biến toàn cục 'Echo' cũng chỉ được đọc
            }
        }
    },
    {
        files: ["**/*.vue"],
        plugins: { vue: vuePlugin },
        languageOptions: {
            parser: vueParser,
            ecmaVersion: 2022,
            sourceType: "module",
            globals: {
                ...globals.browser
            },
            parserOptions: {
                ecmaFeatures: {
                    jsx: true
                }
            }
        },
        rules: {
            "vue/multi-word-component-names": "off"
        }
    },
    // JSON configuration
    { 
        files: ["**/*.json"], 
        plugins: { json: jsonPlugin }, 
        language: "json/json", 
        extends: ["json/recommended"] 
    },
    { 
        files: ["**/*.jsonc"], 
        plugins: { json: jsonPlugin }, 
        language: "json/jsonc", 
        extends: ["json/recommended"] 
    },
    // CSS configuration
    { 
        files: ["**/*.css"], 
        plugins: { css: cssPlugin }, 
        language: "css/css", 
        extends: ["css/recommended"] 
    },
    ]
)
