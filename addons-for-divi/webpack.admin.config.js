/**
 * Dashboard v2 build — admin/src → admin/build.
 *
 * Separate from webpack.config.js (legacy v1 dashboard + module bundles),
 * which stays untouched while both UIs ship. React 18 is BUNDLED (via the
 * react18/react-dom18 npm aliases) because the plugin's WP floor predates
 * core's react-jsx-runtime handle; the legacy bundle keeps its own React 17.
 * Only wp.apiFetch / wp.i18n come from WordPress.
 */
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
    mode: isProduction ? 'production' : 'development',
    devtool: isProduction ? false : 'source-map',
    entry: { index: './admin/src/index.js' },

    output: {
        path: path.resolve(__dirname, 'admin/build'),
        filename: '[name].js',
        clean: true,
    },

    externals: {
        '@wordpress/api-fetch': 'wp.apiFetch',
        '@wordpress/i18n': 'wp.i18n',
    },

    resolve: {
        extensions: ['.js', '.jsx'],
        // Absolute aliases so the (possibly npm-linked) @plugpress/ui package
        // and its deps (radix, sonner) all share THIS repo's React 18 copy.
        alias: {
            'react/jsx-runtime': require.resolve('react18/jsx-runtime'),
            'react/jsx-dev-runtime': require.resolve('react18/jsx-dev-runtime'),
            'react-dom/client': require.resolve('react-dom18/client'),
            react$: path.dirname(require.resolve('react18/package.json')),
            'react-dom$': path.dirname(require.resolve('react-dom18/package.json')),
        },
    },

    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            '@babel/preset-env',
                            ['@babel/preset-react', { runtime: 'automatic' }],
                        ],
                    },
                },
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
            },
        ],
    },

    plugins: [new MiniCssExtractPlugin({ filename: '[name].css' })],

    performance: { hints: false },
    stats: { children: false, modules: false },
};
