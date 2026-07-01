import codecs

with open("Index_backup_extract.txt", "r", encoding="utf-8") as f:
    raw = f.read()

# If it's a JSON string, it will be surrounded by quotes and contain literal \n
if raw.startswith('"') and raw.endswith('"'):
    # decode the literal escapes
    decoded = codecs.decode(raw[1:-1], "unicode_escape")
    with open(
        "C:\\Users\\User\\Desktop\\system\\Gaw-at Go\\frontendMobile\\src\\Pages\\Tourist\\Itinerary\\Index.jsx",
        "w",
        encoding="utf-8",
    ) as out:
        out.write(decoded)
    print("Successfully decoded and restored Index.jsx")
else:
    print("Not a JSON string?")
