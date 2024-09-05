const axios = require('axios');
const cheerio = require('cheerio');

// Função para obter e processar o HTML da URL fornecida
async function fetchAndParseHtml(url) {
    try {
        // Faz a requisição para obter o conteúdo HTML
        const { data: html } = await axios.get(url);
        
        // Carrega o HTML no cheerio
        const $ = cheerio.load(html);
        
        // Seleciona os artigos
        const articles = $("article.item.tvshows");
        
        const results = [];
        
        // Percorre cada artigo e extrai os dados
        articles.each((index, article) => {
            const titleNode = $(article).find("div.data h3 a").first();
            const dateNode = $(article).find("div.data span").first();
            const descNode = $(article).find("div.texto").first();
            const genreNode = $(article).find("div.genres div.mta a").first();
            const imgNode = $(article).find("div.poster img").first();
            
            const title = titleNode.text().trim() || '';
            const url = titleNode.attr('href') || '';
            const date = dateNode.text().trim() || '';
            const description = descNode.text().trim() || '';
            const genre = genreNode.text().trim() || '';
            const image = imgNode.attr('src') || '';
            
            // Adiciona ao resultado somente se o título não estiver vazio
            if (title) {
                const result = { title };
                
                if (url) {
                    result.url = url;
                }
                
                if (date) {
                    result.date = date;
                }
                
                if (description) {
                    result.description = description;
                }
                
                if (genre) {
                    result.genre = genre;
                }
                
                if (image) {
                    result.image = image;
                }
                
                results.push(result);
            }
        });
        
        return results;
    } catch (error) {
        return { error: `Erro ao acessar a URL: ${error.message}` };
    }
}

// URL a ser requisitada
const url = 'https://novelasflixbr.net/novelas/';

// Obtém e processa os dados
fetchAndParseHtml(url).then((data) => {
    // Define o tipo de conteúdo como JSON
    console.log(JSON.stringify(data, null, 2));
}).catch((error) => {
    console.error('Erro na requisição:', error.message);
});