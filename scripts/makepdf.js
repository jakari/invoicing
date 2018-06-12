const puppeteer = require('puppeteer');
const path = require('path');

if (typeof process.argv.length < 4) {
  console.log('Error: missing input and/or output arguments, usage: node makepdf.js input.html output.pdf');
  process.exit();
}

(async() => {
  const browser = await puppeteer.launch();
const page = await browser.newPage();
await page.setViewport({width: 1588, height: 2244, deviceScaleFactor: 2});
await page.goto('file://' + process.argv[2], {waitUntil: 'networkidle2'});
await page.pdf({path: process.argv[3], format: 'A4'});

await browser.close();
})();
