import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const [standingsUrl, fixturesUrl, outFile] = process.argv.slice(2);

if (!standingsUrl || !fixturesUrl || !outFile) {
  console.error('Usage: node scripts/soccerway_scrape.mjs <standingsUrl> <fixturesUrl> <outFile>');
  process.exit(1);
}

function extract(feed, key) {
  const re = new RegExp(`(?:^|¬)${key}÷([^¬]*)`);
  const m = feed.match(re);
  return m ? m[1].trim() : '';
}

async function scrapeStandings(page, url) {
  let standingsFeed = '';

  const onResponse = async (response) => {
    const responseUrl = response.url();
    if (!responseUrl.includes('/x/feed/to_')) return;
    if (response.status() !== 200) return;

    try {
      const body = await response.text();
      if (body.includes('¬~TR÷') && body.includes('¬TN÷') && body.includes('¬TP÷')) {
        standingsFeed = body;
      }
    } catch {
      // Ignore network parsing errors for non-critical feed calls.
    }
  };

  page.on('response', onResponse);
  await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(4000);
  page.off('response', onResponse);

  if (!standingsFeed) return [];

  const blocks = standingsFeed.split('¬~TR÷').slice(1);
  const rows = [];

  for (const block of blocks) {
    const rank = parseInt(block.split('¬')[0], 10);
    const team = extract(block, 'TN');
    const pointsRaw = extract(block, 'TP');
    const points = pointsRaw ? parseInt(pointsRaw, 10) : null;

    if (!Number.isInteger(rank) || !team) continue;

    rows.push({ intRank: rank, strTeam: team, intPoints: Number.isInteger(points) ? points : null });
  }

  return rows.sort((a, b) => a.intRank - b.intRank);
}

async function scrapeFixtures(page, url) {
  await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(3000);

  const fixturesFeed = await page.evaluate(() => {
    return window.cjs?.initialFeeds?.['summary-fixtures']?.data || '';
  });

  if (!fixturesFeed) return [];

  const blocks = fixturesFeed.split('¬~AA÷').slice(1);
  const rows = [];

  for (const block of blocks) {
    const tsRaw = extract(block, 'AD');
    const home = extract(block, 'AE');
    const away = extract(block, 'AF');
    if (!home || !away) continue;

    const ts = tsRaw ? Number(tsRaw) : 0;
    let dateEvent = '';
    let strTime = '';

    if (Number.isFinite(ts) && ts > 0) {
      const d = new Date(ts * 1000);
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const hh = String(d.getHours()).padStart(2, '0');
      const mm = String(d.getMinutes()).padStart(2, '0');
      dateEvent = `${y}-${m}-${day}`;
      strTime = `${hh}:${mm}`;
    }

    rows.push({
      _ts: ts,
      dateEvent,
      strTime,
      strHomeTeam: home,
      strAwayTeam: away,
    });
  }

  return rows
    .sort((a, b) => (a._ts || 0) - (b._ts || 0))
    .slice(0, 40)
    .map(({ _ts, ...rest }) => rest);
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();

try {
  const standings = await scrapeStandings(page, standingsUrl);
  const fixtures = await scrapeFixtures(page, fixturesUrl);

  const payload = {
    source: 'soccerway',
    fetched_at: new Date().toISOString(),
    standings,
    fixtures,
  };

  await fs.mkdir(path.dirname(outFile), { recursive: true });
  await fs.writeFile(outFile, JSON.stringify(payload, null, 2), 'utf8');
} finally {
  await browser.close();
}
