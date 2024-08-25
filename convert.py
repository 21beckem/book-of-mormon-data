import json

with open('MB_chapters.json', 'r') as f:
    MB_chapters = json.load(f)

    for BOOK in MB_chapters.keys():
        thisNewBook = {}
        thisNewBook['link-id'] = MB_chapters[BOOK][0][0].replace('/study/scriptures/bofm/', '').replace('/1?lang=eng', '')
        thisNewBook['chapters'] = len(MB_chapters[BOOK])
        for chap in MB_chapters[BOOK]:
            with open(f'MB-old/{chap[1]}.json', 'r') as f:
                MB_data = json.load(f)
                MB_data = list(map(lambda x: x[1], MB_data))
                thisNewBook[chap[1].replace(BOOK + ' ', '')] = MB_data
        # save to json
        with open(f'MB/{BOOK}.json', 'w') as f:
            json.dump(thisNewBook, f, indent=4)
