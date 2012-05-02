donaldmerand.com - the PHP version
==================================

aka _The Blogulator_

This is the source code for the _old_ version of <http://donaldmerand.com>. I wrote a lengthy article about my reasons/justifications for doing things this way. [You can read it if you like](http://donaldmerand.com/code/2012/02/29/about-this-site.html).


Highlights
----------

- Entirely filesystem-based storage. No databases.
- Page editing via either a web interface (`admin/`), or by manually placing pages in the `data/` folder and any uploaded files in the "files" folder.
  - Note that if you manually place files, you'll want to run `php recalculate_cache.php` to recalculate the page cache file.
- Web editing has an editor and preview courtesy of [Hashify](https://bitbucket.org/davidchambers/hashify-editor), and file upload courtesy of [valums on GitHub](https://github.com/valums/file-uploader).
- [Markdown](http://daringfireball.net/projects/markdown/) for page formatting.
- Page search using a `grep` wrapper.
- Decent URLs via manual hacking of `.htaccess` - Apache-only, natch.

I'm releasing the code as-is, you know, for internet posterity. I'm no PHP wizard - it could be significantly less ugly, particularly in the DRY and modularization-o'-code arenas. Oh well - that's what you get when you write code you don't intend to open-source.


License
-------

The following directories and their contents are Copyright Donald L. Merand. You may not reuse anything therein without my permission:

    _data/

All other directories and files are MIT Licensed.
