from fastapi import FastAPI

app = FastAPI()

@app.get("/getInfo")
def read_root():
    return {"Hello": "World"}