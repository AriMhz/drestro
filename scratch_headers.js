const headers = new Headers({
  'X-API-KEY': 'mykey',
  'X-Api-Key': 'mykey'
});

console.log(headers.get('x-api-key'));
