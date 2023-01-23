module.exports = {
    plugins: {
        "postcss-easy-import": {
            path: ["./src/css"],
            prefix: "_",
            extensions: [".css", ".scss"],
        },
        "tailwindcss/nesting": {},
        tailwindcss: {},
        autoprefixer: {},
        ...(process.env.NODE_ENV === "production" ? { cssnano: {} } : {}),
    },
};