const path = require("path");
const glob = require("glob");

const miniCss = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

// Функция для генерации точек входа
const generateEntries = () => {
	const entries = {};
	const files = glob.sync("./sport/football/js/**/index.js", {
		cwd: path.resolve(__dirname),
	});

	console.log("Found files:", files); // Для отладки

	files.forEach((file) => {
		const relativePath = path.relative("./sport", file); // Генерируем относительный путь от папки sport
		const entryName = relativePath.replace(/\/index\.js$/, ""); // Убираем index.js из имени
		entries[entryName] = `./${file}`; // Добавляем относительный путь с "./"
	});

	console.log("Dynamic entries:", entries); // Для отладки
	return entries;
};

// Настройки Webpack
module.exports = {
	mode: "production",
	entry: {
		main: "./index.js",
		api: "./api.js",
		...generateEntries(),
	},
	output: {
		path: path.resolve(__dirname, "assets"),
		filename: (pathData) => {
			if (pathData.chunk.name.includes("/")) {
				return `../sport/${pathData.chunk.name}/index.min.js`;
			}
			return "[name].js";
		},
	},
	module: {
		rules: [
			{
				test: /\.html$/,
				use: "html-loader",
			},
			{
				test: /\.js$/,
				use: "babel-loader",
			},
			{
				test: /\.(scss|css)$/,
				use: [
					miniCss.loader,
					{
						loader: "css-loader",
						options: { url: false },
					},
					{
						loader: "sass-loader",
					},
				],
			},
		],
	},
	plugins: [
		new miniCss({
			filename: "../style.css",
		}),
	],
	optimization: {
		minimizer: [`...`, new CssMinimizerPlugin()],
	},
};
