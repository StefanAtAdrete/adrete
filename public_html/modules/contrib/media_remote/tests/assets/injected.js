// This is here just to test formatters for potential security vulnerabilities
// in their REGEXes. Depending on how the embed snippet is constructed, you
// can try to create a media item injecting this file and see if this gets
// executed when the media item is rendered. The URL to source this script
// would have a format similar to this one:
// https://[your-domain]/modules/contrib/media_remote/tests/assets/injected.js?foo=bar.
document.body.innerHTML = 'Womp womp, this should not have happened.'
