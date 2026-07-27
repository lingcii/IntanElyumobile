import json
import sys

log_path = r"C:\Users\User\.gemini\antigravity\brain\30868d92-4a90-4130-afaf-6f0d60342c65\.system_generated\logs\overview.txt"

for line in reversed(list(open(log_path, "r", encoding="utf-8"))):
    try:
        data = json.loads(line)
        if "tool_calls" in data:
            for call in data["tool_calls"]:
                if call["name"] in (
                    "replace_file_content",
                    "multi_replace_file_content",
                    "write_to_file",
                ):
                    args = call.get("args", {})

                    # Also look at args string if it's a string
                    if isinstance(args, str):
                        args = json.loads(args)

                    target = args.get("TargetFile", "") or args.get("AbsolutePath", "")
                    if "Index.jsx" in target:

                        if call["name"] == "replace_file_content":
                            code = args.get("ReplacementContent", "")
                            if len(code) > 10000:
                                with open(
                                    "C:\\Users\\User\\Desktop\\system\\Gaw-at Go\\frontendMobile\\src\\Pages\\Tourist\\Itinerary\\Index.jsx",
                                    "w",
                                    encoding="utf-8",
                                ) as out:
                                    out.write(code)
                                print(
                                    f"Restored from replace_file_content, length {len(code)}"
                                )
                                sys.exit(0)

                        elif call["name"] == "multi_replace_file_content":
                            chunks = args.get("ReplacementChunks", [])
                            if isinstance(chunks, str):
                                try:
                                    chunks = json.loads(chunks)
                                except:
                                    pass

                            for chunk in chunks:
                                if isinstance(chunk, dict):
                                    code = chunk.get("ReplacementContent", "")
                                    if len(code) > 10000:
                                        with open(
                                            "C:\\Users\\User\\Desktop\\system\\Gaw-at Go\\frontendMobile\\src\\Pages\\Tourist\\Itinerary\\Index.jsx",
                                            "w",
                                            encoding="utf-8",
                                        ) as out:
                                            out.write(code)
                                        print(
                                            f"Restored from multi_replace_file_content, length {len(code)}"
                                        )
                                        sys.exit(0)
    except Exception as e:
        pass
