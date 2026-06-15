# ruff: noqa
# Copyright 2026 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     https://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

import os
import glob
from google.adk.agents import Agent
from google.adk.apps import App
from google.adk.models import Gemini
from google.genai import types

# Use Google AI Studio API Key (GEMINI_API_KEY env var) instead of Vertex AI
os.environ["GOOGLE_GENAI_USE_VERTEXAI"] = "False"


def search_docs(query: str) -> str:
    """Searches the system documentation files for occurrences of a keyword or query.
    Useful to locate which file contains information about a topic.

    Args:
        query: The keyword or query to search for in the docs.

    Returns:
        A list of matching lines and context from the documentation files.
    """
    results = []
    doc_files = ["README.md", "walkthrough.md", "deployment_guide.md"]
    paths = []
    
    # Try parent directory (when running from app/) and workspace root
    for f_name in doc_files:
        paths.append(os.path.join(".", f_name))
        paths.append(os.path.join("..", f_name))
        paths.append(os.path.join("C:\\Users\\ideba\\.gemini\\antigravity-ide\\brain\\46e94683-8888-46b8-8ca1-d4f287e84577", f_name))
        paths.append(os.path.join("f:\\Arsitektur Sistem PSTI Kota Bandung", f_name))
        
    seen_files = set()
    for p in paths:
        if os.path.exists(p) and os.path.isfile(p):
            f_basename = os.path.basename(p)
            if f_basename in seen_files:
                continue
            seen_files.add(f_basename)
            
            try:
                with open(p, "r", encoding="utf-8") as f:
                    content = f.read()
                lines = content.split("\n")
                matches = []
                for i, line in enumerate(lines):
                    if query.lower() in line.lower():
                        matches.append(f"L{i+1}: {line.strip()}")
                if matches:
                    results.append(f"--- File: {f_basename} ---\n" + "\n".join(matches[:10]))
            except Exception as e:
                results.append(f"Error reading {f_basename}: {e}")
                
    if not results:
        return f"No matches found for query: '{query}' in documentation files."
    return "\n\n".join(results)


def read_doc_file(filename: str) -> str:
    """Reads the full content of a specific documentation file to answer questions in detail.
    Available files are: 'README.md', 'walkthrough.md', 'deployment_guide.md'.

    Args:
        filename: The exact name of the file to read (e.g. 'README.md', 'walkthrough.md', or 'deployment_guide.md').

    Returns:
        The text content of the requested documentation file.
    """
    safe_filename = os.path.basename(filename)
    paths = [
        os.path.join(".", safe_filename),
        os.path.join("..", safe_filename),
        os.path.join("f:\\Arsitektur Sistem PSTI Kota Bandung", safe_filename),
        os.path.join("C:\\Users\\ideba\\.gemini\\antigravity-ide\\brain\\46e94683-8888-46b8-8ca1-d4f287e84577", safe_filename)
    ]
    for p in paths:
        if os.path.exists(p) and os.path.isfile(p):
            try:
                with open(p, "r", encoding="utf-8") as f:
                    return f.read()
            except Exception as e:
                return f"Error reading file {safe_filename}: {e}"
                
    return f"File '{filename}' not found. Available files are: 'README.md', 'walkthrough.md', 'deployment_guide.md'."


root_agent = Agent(
    name="root_agent",
    model=Gemini(
        model="gemini-2.5-flash",  # Use the latest fast model
        retry_options=types.HttpRetryOptions(attempts=3),
    ),
    instruction=(
        "You are a helpful support agent for the PSAMS (PSTI Sport Analytics & Management System) Kota Bandung. "
        "Your job is to answer questions about the system using the provided documentation files (README.md, walkthrough.md, "
        "and deployment_guide.md). Always use the search_docs and read_doc_file tools to retrieve accurate factual details "
        "from the docs before answering. Do not assume or hallucinate information not present in the docs."
    ),
    tools=[search_docs, read_doc_file],
)

app = App(
    root_agent=root_agent,
    name="app",
)
