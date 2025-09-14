// scripts/index-docs-production.mjs (Node 18+)
// Production version of the docs indexer for heartfeltdagger.com
import fs from 'node:fs';
import path from 'node:path';
import axios from 'axios';
import * as cheerio from 'cheerio';
import MiniSearch from 'minisearch';
import { URL } from 'node:url';

const START_URL = process.env.DOCS_START_URL || 'https://heartfeltdagger.com/reference/';
const ORIGIN = new URL(START_URL).origin;
const SCOPE_PATH = '/reference';

const seen = new Set();
const queue = [START_URL];
const docs = [];
let id = 1;

// Define all the reference pages we want to index
const REFERENCE_PAGES = [
    // Introduction
    'what-is-this',
    'the-basics',
    
    // Character Creation
    'character-creation',
    
    // Core Materials
    'domains',
    'classes',
    'ancestries', 
    'communities',
    
    // Domain abilities
    'arcana-abilities',
    'blade-abilities',
    'bone-abilities',
    'codex-abilities',
    'grace-abilities',
    'midnight-abilities',
    'sage-abilities',
    'splendor-abilities',
    'valor-abilities',
    
    // Core Mechanics
    'flow-of-the-game',
    'core-gameplay-loop',
    'the-spotlight',
    'turn-order-and-action-economy',
    'making-moves-and-taking-action',
    'combat',
    'stress',
    'attacking',
    'maps-range-and-movement',
    'conditions',
    'downtime',
    'death',
    'additional-rules',
    'leveling-up',
    'multiclassing',
    
    // Equipment section
    'equipment',
    'weapons',
    'combat-wheelchair',
    'armor',
    'loot',
    'consumables',
    'gold',
    
    // Running an Adventure (GM Guidance)
    'gm-guidance',
    'core-gm-mechanics',
    'adversaries',
    'environments',
    'additional-gm-guidance',
    'campaign-frames'
];

function inScope(u) {
    const url = new URL(u, ORIGIN);
    return url.origin === ORIGIN && url.pathname.startsWith(SCOPE_PATH);
}

console.log('Starting documentation indexing for production...');
console.log(`Base URL: ${START_URL}`);

// Instead of crawling, we'll directly index our known pages
for (const page of REFERENCE_PAGES) {
    const url = `${START_URL}${page}`;
    
    try {
        console.log(`Indexing: ${url}`);
        const { data: html } = await axios.get(url, { 
            timeout: 30000, // Longer timeout for production
            headers: {
                'User-Agent': 'DaggerHeart-Indexer/1.0 (Production)'
            }
        });
        
        const $ = cheerio.load(html);
        
        // Extract title from multiple possible sources
        const title = 
            $('meta[property="og:title"]').attr('content') ||
            $('title').text().trim() ||
            $('h1').first().text().trim() ||
            page.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        // Grab only the main content marked with data-search-body
        const body = $('[data-search-body]').text().replace(/\s+/g, ' ').trim();
        
        if (body.length > 0) {
            docs.push({ 
                id: id++, 
                title: title.replace(/\s+/g, ' ').trim(), 
                url: url, 
                content: body,
                page: page
            });
            console.log(`✓ Indexed: ${title} (${body.length} characters)`);
        } else {
            console.warn(`⚠ No content found for: ${url}`);
        }
    } catch (e) {
        console.warn(`✗ Skip (error): ${url} - ${e.message}`);
    }
}

console.log(`\nCrawled ${docs.length} docs`);

if (docs.length === 0) {
    console.error('No documents were indexed. Please check your server is running and pages are accessible.');
    process.exit(1);
}

// Build a compact MiniSearch index
const mini = new MiniSearch({
    fields: ['title', 'content'],
    storeFields: ['title', 'url', 'page'],
    searchOptions: { 
        prefix: true, 
        fuzzy: 0.2,
        combineWith: 'AND'
    },
});

mini.addAll(docs);

// Save serialized index to public
const outDir = 'public';
fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(path.join(outDir, 'docs-index.json'), JSON.stringify(mini.toJSON()));

console.log(`\n✓ Wrote public/docs-index.json`);
console.log(`✓ Index contains ${docs.length} documents`);
console.log('✓ Production search index ready for deployment');

// Also create a backup with timestamp
const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const backupFile = path.join(outDir, `docs-index-${timestamp}.json`);
fs.writeFileSync(backupFile, JSON.stringify(mini.toJSON()));
console.log(`✓ Backup created: ${backupFile}`);
