import re

TOKEN_PATTERNS = {
    "{{}}": re.compile(r"\{\{\s*([a-zA-Z0-9_.#\/\-]+)\s*\}\}"),
    "[[]]": re.compile(r"\[\[\s*([a-zA-Z0-9_.#\/\-]+)\s*\]\]"),
    "<<>>": re.compile(r"<<\s*([a-zA-Z0-9_.#\/\-]+)\s*>>"),
}


def sanitize_key(key: str) -> str:
    return re.sub(r"[^a-zA-Z0-9_.#\/\-]", "", key).strip()


def infer_type(key: str, context: str) -> str:
    txt = f"{key} {context}".lower()
    if any(x in txt for x in ["amount", "price", "cost", "budget", "aed", "usd", "eur"]):
        return "currency"
    if any(x in txt for x in ["date", "deadline", "start", "end"]):
        return "date"
    if any(x in txt for x in ["duration", "count", "qty", "number", "total"]):
        return "number"
    if any(x in txt for x in ["scope", "background", "description", "summary", "notes"]):
        return "longtext"
    return "string"


def find_placeholders(text: str) -> list[tuple[str, str]]:
    found: list[tuple[str, str]] = []
    for fmt, pattern in TOKEN_PATTERNS.items():
        for m in pattern.finditer(text or ""):
            found.append((sanitize_key(m.group(1)), fmt))
    return found


def replace_tokens(text: str, values: dict[str, str], keep_unanswered: bool = False) -> str:
    result = text
    for fmt, pattern in TOKEN_PATTERNS.items():
        def repl(match: re.Match[str]) -> str:
            key = sanitize_key(match.group(1))
            val = values.get(key)
            if val is None or val == "":
                return match.group(0) if keep_unanswered else ""
            return str(val)

        result = pattern.sub(repl, result)
    return result
