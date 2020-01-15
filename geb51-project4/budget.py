"""
    Budget Boi (temp name)

    A simple budgeting application, suitable for one person.

"""
import json, os
from datetime import datetime
from flask import Flask, request, session, url_for, redirect, render_template, abort, g, flash, _app_ctx_stack
from flask_restful import reqparse, abort, Api, Resource

app = Flask(__name__)
api = Api(app)

CATEGORIES = {
    'cat_1': {'name':'Uncategorized', 'total': 0.00}
    # 'cat_2': {'name':'Food', 'limit': 500.00, 'diff': 500.00},
    # 'cat_3': {'name':'Rent', 'limit': 800.00, 'diff': 800.00}
}

PURCHASES = {
    # 'pur_1': {'des':'got burgers', 'cat':'Food', 'amt': 3.00, 'date':'2019-12-01'},
    # 'pur_2': {'des':'paid Nov rent', 'cat':'Rent', 'amt': 800.00, 'date':'2019-11-02'}
}

DATE = {
    'day' : 1,
    'month' : 1,
    'year' : 0000
}


def cat_doesnt_exist(cat_id):
    if cat_id not in CATEGORIES:
        abort(404, message="Category {} doesn't exist".format(cat_id))

def pur_doesnt_exist(pur_id):
    if pur_id not in PURCHASES:
        abort(404, message="Purchase {} doesn't exist".format(pur_id))

catParser = reqparse.RequestParser()
catParser.add_argument('name')
catParser.add_argument('limit')
catParser.add_argument('diff')
purParser = reqparse.RequestParser()
purParser.add_argument('des')
purParser.add_argument('cat')
purParser.add_argument('amt')
purParser.add_argument('date')
datParser = reqparse.RequestParser()
datParser.add_argument('year')
datParser.add_argument('month')
datParser.add_argument('day')


@app.route("/")
def index_page():
    return render_template("index.html")


# Categories
# shows Categories name and limit, and lets you delete a Categories
class Categories(Resource):
    def get(self):
        # dateForm = "%Y-%m-%d"
        # # Get sum of purchases for each category
        # for c in CATEGORIES:
        #     if CATEGORIES[c]['name'] == "Uncategorized":
        #         total = 0
        #         for p in PURCHASES:
        #             if PURCHASES[p]['cat'] == "Uncategorized":
        #                 pdate = datetime.strptime(PURCHASES[p]['date'], dateForm)
        #                 if pdate.month == DATE['month'] and pdate.year == DATE['year']:
        #                     total = total + PURCHASES[p]['amt']
        #         CATEGORIES[c]['total'] = round(total, 2)
        #     else:
        #         diff = CATEGORIES[c]['limit']
        #         name = CATEGORIES[c]['name']
        #         # Search purchases
        #         for p in PURCHASES:
        #             if PURCHASES[p]['cat'] == name:
        #                 pdate = datetime.strptime(PURCHASES[p]['date'], dateForm)
        #                 if pdate.month == DATE['month'] and pdate.year == DATE['year']:
        #                     diff = diff - PURCHASES[p]['amt']
        #         # Apply new difference
        #         CATEGORIES[c]['diff'] = round(diff, 2)
        return CATEGORIES

    def post(self):
        cat_id = int(max(CATEGORIES.keys()).lstrip('cat_')) + 1
        cat_id = "cat_%i" % cat_id
        args = catParser.parse_args()
        CATEGORIES[cat_id] = {'name': args['name'], 'limit': round(float(args['limit']), 2), 'diff': round(float(args['diff']), 2)}
        return CATEGORIES[cat_id], 201

class Category(Resource):
    def get(self, cat_id):
        cat_doesnt_exist(cat_id)
        return CATEGORIES[cat_id]
    
    def delete(self, cat_id):
        # Delete the category
        cat_doesnt_exist(cat_id)
        catName = CATEGORIES[cat_id]['name']
        del CATEGORIES[cat_id]
        # Change purchases to Uncategorized?
        for p in PURCHASES:
            if PURCHASES[p]['cat'] == catName:
                PURCHASES[p]['cat'] = "Uncategorized"

        dateForm = "%Y-%m-%d"
        # Update sum of purchases for each category
        for c in CATEGORIES:
            if CATEGORIES[c]['name'] == "Uncategorized":
                total = 0
                for p in PURCHASES:
                    if PURCHASES[p]['cat'] == "Uncategorized":
                        pdate = datetime.strptime(PURCHASES[p]['date'], dateForm)
                        if pdate.month == DATE['month'] and pdate.year == DATE['year']:
                            total = total + PURCHASES[p]['amt']
                CATEGORIES[c]['total'] = round(total, 2)
            else:
                diff = CATEGORIES[c]['limit']
                name = CATEGORIES[c]['name']
                # Search purchases
                for p in PURCHASES:
                    if PURCHASES[p]['cat'] == name:
                        pdate = datetime.strptime(PURCHASES[p]['date'], dateForm)
                        if pdate.month == DATE['month'] and pdate.year == DATE['year']:
                            diff = diff - PURCHASES[p]['amt']
                # Apply new difference
                CATEGORIES[c]['diff'] = round(diff, 2)
        
        return '', 204

class Purchases(Resource):
    def get(self):
        return PURCHASES

    def post(self):
        pur_id = 1
        if(len(PURCHASES) > 0):
            pur_id = int(max(PURCHASES.keys()).lstrip('pur_')) + 1
        pur_id = "pur_%i" % pur_id
        args = purParser.parse_args()
        PURCHASES[pur_id] = {'des': args['des'], 'cat':args['cat'], 'amt':round(float(args['amt']), 2), 'date':args['date']}

        dateForm = "%Y-%m-%d"
        # Update sum of purchases for each category
        for c in CATEGORIES:
            if CATEGORIES[c]['name'] == "Uncategorized":
                total = 0
                for p in PURCHASES:
                    if PURCHASES[p]['cat'] == "Uncategorized":
                        pdate = datetime.strptime(PURCHASES[p]['date'], dateForm)
                        if pdate.month == DATE['month'] and pdate.year == DATE['year']:
                            total = total + PURCHASES[p]['amt']
                CATEGORIES[c]['total'] = round(total, 2)
            else:
                diff = CATEGORIES[c]['limit']
                name = CATEGORIES[c]['name']
                # Search purchases
                for p in PURCHASES:
                    if PURCHASES[p]['cat'] == name:
                        pdate = datetime.strptime(PURCHASES[p]['date'], dateForm)
                        if pdate.month == DATE['month'] and pdate.year == DATE['year']:
                            diff = diff - PURCHASES[p]['amt']
                # Apply new difference
                CATEGORIES[c]['diff'] = round(diff, 2)

        return PURCHASES[pur_id], 201

class Date(Resource):
    def post(self):
        args = datParser.parse_args()
        DATE['year'] = int(args['year'])
        DATE['month'] = int(args['month'])
        DATE['day'] = int(args['day'])
        return DATE, 200


api.add_resource(Categories, '/cats')
api.add_resource(Category, '/cats/<cat_id>')
api.add_resource(Purchases, '/purchases')
api.add_resource(Date, '/date')


if __name__ == "__main__":
	app.run(debug=True)