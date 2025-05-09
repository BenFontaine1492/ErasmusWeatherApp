FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Copy requirements and subscriber script
COPY requirements.txt .
COPY subscriber.py .

# Install dependencies globally (inside the container)
RUN pip install --upgrade pip
RUN pip install -r requirements.txt

# Run the subscriber
CMD ["python", "subscriber.py"]
